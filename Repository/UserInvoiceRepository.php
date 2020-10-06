<?php


namespace Repository;

use UserInvoice;

class UserInvoiceRepository
{
    /**
     * get count record by file name
     *
     * @param  string $name
     * @return int
     */
    public static function getCountByFileName(string $name)
    {
        return UserInvoice::where('file_name', $name)->count();
    }

}
