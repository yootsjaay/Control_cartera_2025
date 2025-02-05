<?php 
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
interface SeguroServiceInterface
{
    public function extractToData(UploadedFile $pdf): ?array;
    public function getSeguros(): Collection;
    public function getRamos(int $seguroId): Collection;
}
