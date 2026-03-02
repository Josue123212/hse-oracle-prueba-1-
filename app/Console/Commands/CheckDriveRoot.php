<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class CheckDriveRoot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drive:check-root';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el nombre y contenido de la carpeta raíz configurada en Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId = config('filesystems.disks.google.clientId');
        $clientSecret = config('filesystems.disks.google.clientSecret');
        $refreshToken = config('filesystems.disks.google.refreshToken');
        $folderId = config('filesystems.disks.google.folderId');

        $this->info("Configuración actual:");
        $this->info("Folder ID: " . ($folderId ?: 'No configurado (Raíz de Mi Unidad)'));

        if (!$clientId || !$clientSecret || !$refreshToken) {
            $this->error("Faltan credenciales de Google Drive en la configuración.");
            return;
        }

        try {
            $client = new Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->refreshToken($refreshToken);

            $service = new Drive($client);

            if ($folderId) {
                // Obtener información de la carpeta raíz configurada
                $folder = $service->files->get($folderId, ['fields' => 'id, name, webViewLink']);
                $this->info("--------------------------------------------------");
                $this->info("Nombre de la carpeta raíz: " . $folder->getName());
                $this->info("ID de la carpeta raíz: " . $folder->getId());
                $this->info("Enlace para ver en navegador: " . $folder->getWebViewLink());
                $this->info("--------------------------------------------------");

                $this->info("Contenido de la carpeta (primeros 10):");
                $files = $service->files->listFiles([
                    'q' => "'{$folderId}' in parents and trashed = false",
                    'fields' => 'files(id, name, mimeType, webViewLink)',
                    'pageSize' => 10
                ]);
                
                $fileList = $files->getFiles();
                if (count($fileList) === 0) {
                    $this->warn("  [VACÍA] La carpeta parece estar vacía.");
                } else {
                    foreach ($fileList as $file) {
                        $this->line("- [{$file->getName()}] ({$file->getMimeType()})");
                        $this->line("  Link: {$file->getWebViewLink()}");
                    }
                }

                $this->info("--------------------------------------------------");
                $this->info("Buscando 'test-uploads' en todo el Drive...");
                
                $search = $service->files->listFiles([
                    'q' => "name = 'test-uploads' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                    'fields' => 'files(id, name, parents, webViewLink)',
                ]);

                foreach ($search->getFiles() as $folder) {
                    $this->info("Encontrada carpeta 'test-uploads':");
                    $this->info("  ID: " . $folder->getId());
                    $this->info("  Link: " . $folder->getWebViewLink());
                    $this->info("  Parents: " . implode(', ', $folder->getParents() ?? []));
                    
                    if (in_array($folderId, $folder->getParents() ?? [])) {
                        $this->info("  -> ¡ESTÁ DENTRO DE LA CARPETA CONFIGURADA!");
                    } else {
                        $this->error("  -> ¡NO ESTÁ EN LA CARPETA CONFIGURADA! (Está en otro lugar)");
                    }
                }

                $this->info("--------------------------------------------------");
                $this->info("Buscando archivos 'prueba_raiz_' en todo el Drive...");

                $searchFile = $service->files->listFiles([
                    'q' => "name contains 'prueba_raiz_' and trashed = false",
                    'fields' => 'files(id, name, parents, webViewLink)',
                ]);

                foreach ($searchFile->getFiles() as $file) {
                    $this->info("Encontrado archivo '{$file->getName()}':");
                    $this->info("  Parents: " . implode(', ', $file->getParents() ?? []));
                    if (in_array($folderId, $file->getParents() ?? [])) {
                        $this->info("  -> ¡ESTÁ DENTRO DE LA CARPETA CONFIGURADA!");
                    } else {
                        $this->error("  -> ¡NO ESTÁ EN LA CARPETA CONFIGURADA!");
                    }
                }
            } else {
                $this->info("Como no hay Folder ID, estamos en la raíz de 'Mi Unidad'.");
                $about = $service->about->get(['fields' => 'user']);
                $this->info("Usuario autenticado: " . $about->getUser()->getEmailAddress());
            }

        } catch (\Exception $e) {
            $this->error("Error al conectar con Google Drive: " . $e->getMessage());
        }
    }
}
