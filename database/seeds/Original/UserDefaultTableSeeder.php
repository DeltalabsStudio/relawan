<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserDefaultTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $objUser = new User;
        $objNewUser = new \StdClass;
        if($objUser->getUserByEmail('admin')==null){
            $objNewUser->status_id = '2';
            $objNewUser->name = 'admin posko';
            $objNewUser->email = 'admin@posko.id';
            $objNewUser->password = 'adminPOSKO2018';
            $objNewUser->provider = '';
            $objUser->addUser($objNewUser);
        }
    }
}
