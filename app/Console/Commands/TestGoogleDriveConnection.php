<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGoogleDriveConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Drive connection by listing files and creating a test file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Google Drive Connection...');

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('google');

            // 1. List files in root (or configured folder)
            $this->info('Listing files in root directory:');
            // Using allFiles() instead of files() to better test recursion/access if needed, 
            // but files() is safer for a quick check. Sticking to files() as planned but adding a check.
            $files = $disk->files();
            
            if (empty($files)) {
                $this->warn('No files found (folder might be empty).');
            } else {
                foreach (array_slice($files, 0, 5) as $file) {
                    $this->line("- " . $file);
                }
                if (count($files) > 5) {
                    $this->line("... and " . (count($files) - 5) . " more.");
                }
            }

            // 2. Create a test file
            $fileName = 'test_connection_' . now()->timestamp . '.txt';
            $content = 'This is a test file to verify Google Drive connection from Laravel.';
            
            $this->info("\nAttempting to upload test file: {$fileName}");
            
            $result = $disk->put($fileName, $content);

            if ($result) {
                $this->info("File uploaded successfully!");
                
                // Get URL if possible (depends on adapter support)
                try {
                    $url = $disk->url($fileName);
                    $this->info("File URL: {$url}");
                } catch (\Exception $e) {
                    $this->warn("Could not retrieve URL: " . $e->getMessage());
                }

                // Clean up
                $this->info("Leaving file for verification: {$fileName}");
                // $disk->delete($fileName); // Comentado para persistencia
                $this->info("Test file persisted.");

            } else {
                $this->error("Failed to upload file.");
            }

        } catch (\Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            // $this->error("Trace: " . $e->getTraceAsString()); // Commented out to avoid clutter unless needed
        }
    }
}
