<?php
namespace App\Services;
use App\Models\Employee;
use App\Models\PayrollRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class PayslipBatchService
{
    public function __construct(protected PayslipService $payslipService) {}

    public function startWebGeneration(PayrollRun $payrollRun, bool $force = false): array
    {
        $employees = $this->getEmployeesFromPayroll($payrollRun);
        if ($employees->isEmpty()) throw new RuntimeException('Nenhum holerite encontrado para esta folha.');
        if (!$force && File::exists($this->manifestPath($payrollRun))) {
            $manifest=$this->readManifest($payrollRun);
            if (in_array($manifest['status'] ?? null,['processing','completed'],true)) return $this->publicStatus($manifest);
        }
        if (File::exists($this->runDir($payrollRun))) File::deleteDirectory($this->runDir($payrollRun));
        File::ensureDirectoryExists($this->pdfDir($payrollRun));
        File::ensureDirectoryExists($this->exportsDir());
        $ids=$employees->pluck('id')->map(fn($id)=>(int)$id)->values()->all();
        $manifest=[
            'status'=>'processing','total'=>count($ids),'processed'=>0,'generated'=>0,'failed'=>0,
            'next_index'=>0,'employee_ids'=>$ids,'failures'=>[],'message'=>'Preparando geração dos holerites.',
            'zip_path'=>null,'started_at'=>now()->toIso8601String(),'updated_at'=>now()->toIso8601String(),
        ];
        $this->writeManifest($payrollRun,$manifest);
        return $this->publicStatus($manifest);
    }

    public function processWebChunk(PayrollRun $payrollRun, int $chunkSize = 5): array
    {
        $chunkSize=max(1,min(10,$chunkSize));
        $manifest=$this->readManifest($payrollRun);
        if (in_array($manifest['status'] ?? null,['completed','failed'],true)) return $this->publicStatus($manifest);
        $ids=collect($manifest['employee_ids'] ?? [])->slice((int)($manifest['next_index'] ?? 0),$chunkSize)->values();
        if ($ids->isEmpty()) return $this->finalize($payrollRun,$manifest);
        $employees=Employee::query()->whereIn('id',$ids)->get()->keyBy('id');
        foreach($ids as $employeeId){
            $employee=$employees->get((int)$employeeId);
            $manifest['processed']++;
            $manifest['next_index']++;
            if(!$employee){
                $manifest['failed']++;
                $manifest['failures'][]=['employee_id'=>(int)$employeeId,'employee_name'=>'-','message'=>'Colaborador não encontrado.'];
                continue;
            }
            try{
                $pdf=$this->payslipService->generate($payrollRun,$employee);
                File::put($this->pdfDir($payrollRun).DIRECTORY_SEPARATOR.$this->pdfName($employee,$payrollRun),$pdf->output());
                unset($pdf); gc_collect_cycles();
                $manifest['generated']++;
            }catch(Throwable $e){
                $manifest['failed']++;
                $manifest['failures'][]=['employee_id'=>$employee->id,'employee_name'=>$employee->name ?? '-','message'=>$e->getMessage()];
                Log::error('Erro ao gerar holerite em lote via web.',['payroll_run_id'=>$payrollRun->id,'employee_id'=>$employee->id,'message'=>$e->getMessage()]);
            }
        }
        $manifest['message']=sprintf('%d de %d holerites processados.',$manifest['processed'],$manifest['total']);
        $manifest['updated_at']=now()->toIso8601String();
        $this->writeManifest($payrollRun,$manifest);
        return $manifest['processed'] >= $manifest['total'] ? $this->finalize($payrollRun,$manifest) : $this->publicStatus($manifest);
    }

    public function webStatus(PayrollRun $payrollRun): array
    {
        if(!File::exists($this->manifestPath($payrollRun))) return ['status'=>'not_started','total'=>0,'processed'=>0,'generated'=>0,'failed'=>0,'percentage'=>0,'message'=>'Geração ainda não iniciada.','download_ready'=>false];
        return $this->publicStatus($this->readManifest($payrollRun));
    }

    public function hasPersistentZip(PayrollRun $payrollRun): bool
    {
        $path=$this->zipPath($payrollRun);
        return File::exists($path) && File::size($path)>0;
    }

    public function persistentZipPath(PayrollRun $payrollRun): string { return $this->zipPath($payrollRun); }

    public function resetWebGeneration(PayrollRun $payrollRun): void
    {
        if(File::exists($this->runDir($payrollRun))) File::deleteDirectory($this->runDir($payrollRun));
        if(File::exists($this->manifestPath($payrollRun))) File::delete($this->manifestPath($payrollRun));
        if(File::exists($this->zipPath($payrollRun))) File::delete($this->zipPath($payrollRun));
    }

    protected function finalize(PayrollRun $payrollRun,array $manifest): array
    {
        try{
            if(($manifest['generated'] ?? 0)<=0) throw new RuntimeException('Nenhum holerite pôde ser gerado.');
            if(!empty($manifest['failures'])) $this->writeFailures($payrollRun,$manifest['failures']);
            $zip=new ZipArchive();
            if($zip->open($this->zipPath($payrollRun),ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new RuntimeException('Não foi possível criar o ZIP.');
            try{ foreach(File::files($this->pdfDir($payrollRun)) as $file){ $zip->addFile($file->getRealPath(),$file->getFilename()); } } finally { $zip->close(); }
            if(!$this->hasPersistentZip($payrollRun)) throw new RuntimeException('O ZIP foi criado, mas ficou vazio.');
            $manifest['status']='completed'; $manifest['zip_path']=$this->zipPath($payrollRun);
            $manifest['message']=sprintf('Concluído: %d holerites gerados%s.',$manifest['generated'],$manifest['failed']>0?' e '.$manifest['failed'].' falha(s)':'');
        }catch(Throwable $e){ $manifest['status']='failed'; $manifest['message']=$e->getMessage(); }
        $manifest['updated_at']=now()->toIso8601String();
        $this->writeManifest($payrollRun,$manifest);
        return $this->publicStatus($manifest);
    }

    protected function getEmployeesFromPayroll(PayrollRun $payrollRun): Collection
    {
        return $payrollRun->items()->with('employee')->select(['id','payroll_run_id','employee_id'])->get()->pluck('employee')->filter()->unique('id')->sortBy(fn($e)=>mb_strtoupper((string)($e->name ?? '')))->values();
    }

    protected function publicStatus(array $m): array
    {
        $total=max(0,(int)($m['total'] ?? 0)); $processed=max(0,(int)($m['processed'] ?? 0));
        return ['status'=>$m['status'] ?? 'processing','total'=>$total,'processed'=>$processed,'generated'=>(int)($m['generated'] ?? 0),'failed'=>(int)($m['failed'] ?? 0),'percentage'=>$total>0?min(100,(int)floor(($processed/$total)*100)):0,'message'=>$m['message'] ?? null,'download_ready'=>($m['status'] ?? null)==='completed' && !empty($m['zip_path']) && File::exists($m['zip_path'])];
    }

    protected function readManifest(PayrollRun $run): array
    {
        $data=json_decode((string)File::get($this->manifestPath($run)),true);
        if(!is_array($data)) throw new RuntimeException('Controle da geração inválido.');
        return $data;
    }
    protected function writeManifest(PayrollRun $run,array $m): void { File::ensureDirectoryExists($this->exportsDir()); File::put($this->manifestPath($run),json_encode($m,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),true); }
    protected function writeFailures(PayrollRun $run,array $failures): void
    {
        $lines=['VOKTAR RH & DP - FALHAS NA GERAÇÃO DE HOLERITES','Folha: '.$run->id,str_repeat('-',80)];
        foreach($failures as $f) $lines[]=sprintf('ID %s | %s | %s',$f['employee_id'] ?? '-',$f['employee_name'] ?? '-',$f['message'] ?? '-');
        File::put($this->pdfDir($run).DIRECTORY_SEPARATOR.'FALHAS-NA-GERACAO.txt',implode(PHP_EOL,$lines));
    }
    protected function pdfName(Employee $e,PayrollRun $run): string
    {
        $slug=(string)Str::of($e->name ?? 'colaborador')->ascii()->lower()->replaceMatches('/[^a-z0-9]+/','-')->trim('-');
        return 'holerite-'.($slug ?: 'colaborador').'-emp-'.$e->id.'-folha-'.$run->id.'.pdf';
    }
    protected function exportsDir(): string { return storage_path('app/private/payroll-exports'); }
    protected function runDir(PayrollRun $r): string { return storage_path('app/temp/payslips/web-run-'.$r->id); }
    protected function pdfDir(PayrollRun $r): string { return $this->runDir($r).DIRECTORY_SEPARATOR.'pdfs'; }
    protected function manifestPath(PayrollRun $r): string { return $this->exportsDir().DIRECTORY_SEPARATOR.'holerites-folha-'.$r->id.'.json'; }
    protected function zipPath(PayrollRun $r): string { return $this->exportsDir().DIRECTORY_SEPARATOR.'holerites-folha-'.$r->id.'.zip'; }
}
