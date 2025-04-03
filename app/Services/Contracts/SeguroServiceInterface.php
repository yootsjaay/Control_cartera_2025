<?php 
namespace App\Services\Contracts;
use Illuminate\Http\UploadedFile;
use App\Models\Seguro;
use App\Models\Ramo;
interface SeguroServiceInterface
{
    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo);

}

    