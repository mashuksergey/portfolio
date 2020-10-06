<?php


namespace FileSystemService;


use App\Repository\UserInvoiceRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * This service is responsible for saving invoice files
 *
 * Class UserInvoiceSystemService
 * @package FileSystemService
 */
class UserInvoiceSystemService extends AbstractFileSystemService
{
    /**
     * get folder name
     *
     * @return string
     */
    public function getFolderName(): string
    {
        return 'user_invoices';
    }

    /**
     * get storage
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getStorageObj(): Filesystem
    {
        return Storage::disk('local');
    }

    /**
     * check file_name in db
     *
     * @param string $name
     * @return bool
     */
    public function checkNameInDb(string $name) : bool
    {
        return UserInvoiceRepository::getCountByFileName($name) ? True : False;
    }
}
