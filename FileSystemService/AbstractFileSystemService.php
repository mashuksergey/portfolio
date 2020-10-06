<?php


namespace FileSystemService;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

/**
 * Class AbstractFileSystemService
 * @package FileSystemService
 */
abstract class AbstractFileSystemService
{
    /**
     * Custom storage for saved files
     *
     * @var Storage
     */
    private $storage;

    /**
     * The name of the folder where the files will be stored
     *
     * @var string
     */
    private $folder_name;

    /**
     * Folder name with separator /
     *
     * @var
     */
    private $folder;

    abstract public function getFolderName(): string;
    abstract public function getStorageObj(): Filesystem;
    abstract public function checkNameInDb($name) : bool;

    public function __construct()
    {
        $this->storage = $this->getStorageObj();
        $this->folder_name = $this->getFolderName();
        $this->setFolder($this->folder_name);
    }

    /**
     * set folder name
     *
     * @param string $folder_name
     */
    public function setFolder(string $folder_name)
    {
        $this->folder = $folder_name.'/';
    }

    /**
     * Get folder name
     *
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * get file path in folder
     *
     * @param string $name
     * @return string
     */
    public function getFullPath(string $name)
    {
        return $this->getFolder().$name;
    }

    /**
     * Get Storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * get Default file storage
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createDefaultStorage()
    {
        return Storage::disk(env('FILESYSTEM_DRIVER'));
    }

    /**
     * Check exist file
     *
     * @param string $name
     * @return mixed
     */
    public function exists(string $name)
    {
        return $this->getStorage()->exists($this->getFullPath($name));
    }

    /**
     * get the absolute path to the file
     *
     * @param string $name
     * @return mixed
     */
    public function getFilePath(string $name)
    {
        return $this->getStorage()->path($this->getFullPath($name));
    }

    /**
     * The put method may be used to store raw file contents on a disk.
     *
     * @param string $name
     * @param string $image
     * @return mixed
     */
    public function put(string $name, string $image)
    {
        return $this->getStorage()->put($this->getFullPath($name), $image);
    }

    /**
     * delete file form storage
     *
     * @param string $name
     * @return mixed
     */
    public function delete(string $name)
    {
        return $this->getStorage()->delete($this->getFullPath($name));
    }

    /**
     * The get method may be used to retrieve the contents of a file.
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->getStorage()->get($this->getFullPath($name));
    }

    /**
     * Storage file on storage
     *
     * @param File|UploadedFile $file
     * @return mixed
     */
    public function putFile($file)
    {
        return $this->getStorage()->putFile($this->getFolder(), $file);
    }

    /**
     * Storage file on storage as name
     *
     * @param File|UploadedFile $file
     * @param string $name
     * @return mixed
     */
    public function putFileAs($file, string $name)
    {
        return $this->getStorage()->putFileAs($this->getFolder(), $file, $name);
    }

    /**
     * get uri path
     *
     * @param string $name
     * @return string
     */
    public function getUriPath(string $name)
    {
        return env('DOMAIN_NAME').'/'.$this->getFolder().$name;
    }

    /**
     * get file name and check exist on storage
     *
     * @param File|UploadedFile $file
     * @param int $length
     * @param string $characters
     * @return string
     */
    public function getFileName($file, $length=40, $characters="")
    {
        $ext = $file->getClientOriginalExtension();
        return $this->getFileNameExt($ext, $length, $characters);
    }

    /**
     * get file name with ext
     *
     * @param string $ext
     * @param int $length
     * @param string $characters
     * @return string
     */
    public function getFileNameExt(string $ext, $length=40, $characters="")
    {
        $point = True;
        while ($point) {
            $name = $this->generateRandomString($length, $characters);
            $fullname = $name.'.'.$ext;

            $check = $this->getStorage()->exists($fullname);
            if (!$check) {
                $point = False;
            }
        }

        return $fullname;
    }

    /**
     * Get unique file name from db and check on storage
     *
     * @param File|UploadedFile $file
     * @return string
     */
    public function getFileNameUniqueDB($file)
    {
        $point = True;
        while ($point) {
            $fullname = $this->getFileName($file);

            $checkInDb = $this->checkNameInDb($fullname);
            if ($checkInDb) {
                continue;
            }

            $point = False;
        }

        return $fullname;
    }

    /**
     * generate random string
     *
     * @param int $length
     * @param string $characters
     * @return string
     */
    public function generateRandomString(int $length = 10, string $characters="") {
        $characters = ($characters) ? $characters : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * save file to storage
     *
     * @param File|UploadedFile $file
     * @return bool|string
     */
    public function saveFile($file, $filename="")
    {
        $fullname = ($filename) ? $filename : $this->getFileNameUniqueDB($file);
        $save = $this->putFileAs($file, $fullname);

        $checkExist = False;
        if ($save) {
            $checkExist = $this->exists($fullname);
        }

        return ($checkExist) ? $fullname : False;
    }

    /**
     * delete file from storage
     *
     * @param string $name
     * @return bool
     */
    public function deleteImage(string $name)
    {
        if (!$name) {
            return True;
        }

        $checkExist = $this->exists($name);
        if (!$checkExist) {
            return True;
        }

        $delete = $this->delete($name);
        if (!$delete) {
            return False;
        }

        return True;
    }
}
