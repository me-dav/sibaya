<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MaterialService
{
    private const DISK = 'public';

    private const DIRS = ['image' => 'materials/images', 'audio' => 'materials/audio'];

    public function create(array $data, ?UploadedFile $image = null, ?UploadedFile $audio = null): Material
    {
        $stored = [];

        try {
            return DB::transaction(function () use ($data, $image, $audio, &$stored) {
                foreach (['image' => $image, 'audio' => $audio] as $field => $file) {
                    if ($file) {
                        $data[$field.'_path'] = $stored[] = $file->store(self::DIRS[$field], self::DISK);
                    }
                }

                // Data sudah divalidasi Form Request, jadi aman di-forceFill.
                $material = new Material();
                $material->forceFill($data)->save();

                return $material;
            });
        } catch (Throwable $e) {
            $this->deleteFiles($stored);
            throw $e;
        }
    }

    public function update(
        Material $material,
        array $data,
        ?UploadedFile $image = null,
        ?UploadedFile $audio = null,
        bool $removeImage = false,
        bool $removeAudio = false,
    ): Material {
        $stored = [];
        $old = [];

        try {
            DB::transaction(function () use ($material, $data, $image, $audio, $removeImage, $removeAudio, &$stored, &$old) {
                $files = [
                    'image' => [$image, $removeImage],
                    'audio' => [$audio, $removeAudio],
                ];

                foreach ($files as $field => [$file, $remove]) {
                    $column = $field.'_path';

                    if ($file) {
                        if ($material->{$column}) {
                            $old[] = $material->{$column};
                        }
                        $data[$column] = $stored[] = $file->store(self::DIRS[$field], self::DISK);
                    } elseif ($remove && $material->{$column}) {
                        $old[] = $material->{$column};
                        $data[$column] = null;
                    }
                }

                $material->forceFill($data)->save();
            });
        } catch (Throwable $e) {
            $this->deleteFiles($stored);
            throw $e;
        }

        foreach ($old as $path) {
            $this->deleteIfUnused($path, $material->id);
        }

        return $material;
    }

    /** Materi yang sudah punya rekomendasi tidak boleh dihapus (integritas riwayat). */
    public function canDelete(Material $material): bool
    {
        return ! $material->recommendations()->exists();
    }

    public function delete(Material $material): void
    {
        $paths = array_filter([$material->image_path, $material->audio_path]);

        $material->delete();

        foreach ($paths as $path) {
            $this->deleteIfUnused($path, $material->id);
        }
    }

    private function deleteIfUnused(string $path, int $exceptMaterialId): void
    {
        $stillUsed = Material::query()
            ->where('id', '!=', $exceptMaterialId)
            ->where(fn ($q) => $q->where('image_path', $path)->orWhere('audio_path', $path))
            ->exists();

        if (! $stillUsed) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    private function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
