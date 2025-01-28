<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Livewire\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Store the uploaded file and return its URL.
     *
     * @param UploadedFile|TemporaryUploadedFile $file
     * @param string $directory
     * @return string
     * @throws \Exception
     */
    public function storeFile($file, string $file_name_prefix, string $directory = 'merchant_files'): string
    {
        // Validate the file type
        if (!($file instanceof UploadedFile || $file instanceof TemporaryUploadedFile)) {
            throw new \InvalidArgumentException("Invalid file type. The file must be an instance of UploadedFile or TemporaryUploadedFile.");
        }

        // Get the authenticated merchant
        $merchant = getMerchantAuthUser()->merchant ?? null;
        if (!$merchant || !isset($merchant->id)) {
            throw new \Exception("No authenticated merchant found or merchant ID is missing.");
        }

        // Generate a unique file name without using the original file name
        $fileName = $file_name_prefix . '_' . $merchant->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Create the merchant-specific directory
        $merchantDirectory = $directory . '/merchant_' . $merchant->id;

        // Store the file in the merchant's directory
        $filePath = $file->storeAs($merchantDirectory, $fileName, 'public');

        // Return the file URL
        return url(Storage::url($filePath));
    }

    public function getFileUrl($directory, $filename) : string {
        $file_path = $directory . '/' . $fileName;
        if (Storage::disk('public')->exists($file_path)) { 
            return url(Storage::url($file_path));
        }

        return '';
    }

    public function deleteFile(string $url): bool 
    {
        $base_url = url('storage');
        
        // Remove the base URL part ('http://domain.com/storage') to get the file path
        $file_path = str_replace($base_url . '/', '', $url); 
    
        if (Storage::disk('public')->exists($file_path)) {    
            Storage::disk('public')->delete($file_path);
            return true;
        }

        return false;
    }
    

    
}
