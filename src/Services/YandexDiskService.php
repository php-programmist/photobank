<?php

namespace App\Services;

use App\DTO\YandexFileDto;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Yandex\Disk\DiskClient;

class YandexDiskService
{
    /**@var User*/
    private $user;
    /**@var DiskClient*/
    private $diskClient = null;
    private $root_dir;
    private $preview_size;
    
    public function __construct(Security $security,ParameterBagInterface $params)
    {
        $this->user = $security->getUser();
        $yandex           = $params->get('yandex');
        $this->preview_size = $yandex['preview_size']?:'XS';
        $this->root_dir = '/'.$yandex['root_dir'].'/';
        $this->root_dir = preg_replace('#\/+#','/',$this->root_dir);
        if ($this->user && $this->user->hasValidToken()) {
            $this->diskClient = new DiskClient($this->user->getYandexToken());
            $this->diskClient->setServiceScheme(DiskClient::HTTPS_SCHEME);
        }
    }
    
    public function getLogin()
    {
        if ( !$this->diskClient) {
            return '';
        }
        try{
            $login_data = explode(':',$this->diskClient->getLogin());
            $login = $login_data[1];
            $login = substr($login,0,-4);
        } catch (\Exception $e){
            $login = $e->getMessage();
        }
    
        return $login;
    }
    
    public function getFreeSpace()
    {
        if (! $this->diskClient) {
            return '';
        }
        try{
            $diskSpace  = $this->diskClient->diskSpaceInfo();
            $free_space = round(($diskSpace['availableBytes'] - $diskSpace['usedBytes']) / 1024 / 1024 / 1024,
                    2) . ' ГБ';
        } catch (\Exception $e){
            $free_space = $e->getMessage();
        }
    
        return $free_space;
    }
    
    public function uploadFile($folder, $file_name, $path_to_file)
    {
        if (! $this->diskClient) {
            return false;
        }
        $response = $this->diskClient->uploadFile(
            $this->root_dir.$folder.'/',
            array(
                'path' => $path_to_file,
                'size' => filesize($path_to_file),
                'name' => $file_name
            )
        );
        $code = $response->getStatusCode();
        return $code ==201;
    }
    
    public function createDirectory($folder)
    {
        if (! $this->diskClient) {
            return false;
        }
        $this->diskClient->createDirectory($this->root_dir.$folder);
        return true;
    }
    
    /**
     * @param $folder
     *
     * @return YandexFileDto[]
     */
    public function getFilesOfFolder($folder)
    {
        if (! $this->diskClient) {
            return [];
        }
        $dirContent = $this->diskClient->directoryContents($this->root_dir.$folder);
        $files=[];
        foreach ($dirContent as $dirItem) {
            if ($dirItem['resourceType'] !== 'dir') {
                $preview = $this->diskClient->getImagePreview($this->root_dir.$folder.'/'.$dirItem['displayName'], $this->preview_size);
                $files[] = new YandexFileDto($dirItem['displayName'], $dirItem['contentLength'],
                    $dirItem['creationDate'],$preview['body'],$preview['headers']['content-type']);
            }
        }
        return $files;
    }
    
    public function moveFilesToNewFolder($old_folder, $new_folder)
    {
        if (! $this->diskClient) {
            return false;
        }
        $files = $this->getFilesOfFolder($old_folder);
        if ( ! count($files)) {
            return false;
        }
        foreach ($files as $file) {
            $this->move( $old_folder . '/' . $file->getDisplayName(),  $new_folder . '/' . $file->getDisplayName());
        }
        return true;
    }
    
    public function move($old_path, $new_path)
    {
        if (! $this->diskClient) {
            return false;
        }
        $new_path = $this->urlEncode($new_path);
        return $this->diskClient->move($this->root_dir.$old_path, $this->root_dir.$new_path);
    }
    
    public function delete($path)
    {
        if (! $this->diskClient) {
            return false;
        }
        return $this->diskClient->delete($this->root_dir.$path);
    }
    
    protected function urlEncode($path)
    {
        $path = urlencode($path);
        $path = str_replace('+','%20',$path);
        return str_replace('%2F','/',$path);
    }
    
    public function publishFolder($folder)
    {
        return $this->diskClient->startPublishing($this->root_dir.$folder);
    }
}