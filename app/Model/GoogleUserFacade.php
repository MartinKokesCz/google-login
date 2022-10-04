<?php

declare(strict_types = 1);  

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\Strings;

// BEST FUCKING SHIT EVER https://github.com/thephpleague/oauth2-google/blob/961825b563ea01b7bc6ef5feacae9d155ff9d5db/src/Provider/GoogleUser.php#L60-L65

final class GoogleUserFacade{

    private const
    TableName = "users",
    ColumnSub = "sub",
    ColumnName = "username",
    ColumnGiven_name = "given_name",
    ColumnFamily_name = "family_name",
    ColumnPicture = "picture",
    ColumnEmail = "email",
    ColumnPassword = "password",
    ColumnRole = "role";

    private Passwords $passwords;

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database, Passwords $passwords){
        $this->database = $database;
        $this->passwords  = $passwords;
    }
   
    public function registerFromGoogle($googleUser){

        $this->database->table(SELF::TableName)->insert([
            SELF::ColumnSub => $googleUser->getId(),
            SELF::ColumnName => $googleUser->getId(),
            SELF::ColumnGiven_name => $googleUser->getFirstName(),
            SELF::ColumnFamily_name => $googleUser->getLastName(),
            SELF::ColumnPicture => $googleUser->getAvatar(),
            SELF::ColumnEmail => $googleUser->getEmail(),
            SELF::ColumnPassword => $this->passwords->hash($googleUser->getId()),
            SELF::ColumnRole => "user"
        ]);

        $user["username"] = $googleUser->getId();
        $user["password"] = $googleUser->getId();
       
        return $user;
    }

    public function findByGoogleId($googleId){
        $googleUser = $this->database->table(SELF::TableName)
            ->select(SELF::ColumnSub)
            ->where(SELF::ColumnSub, $googleId)
            ->fetch();        

        if($googleUser){
            $user["username"] = $googleUser[SELF::ColumnSub];
            $user["password"] = $googleUser[SELF::ColumnSub];
           
            return $user;
        }else{
            return false;
        }
    }

    public function findByEmail($googleEmail){
        $this->database->table(SELF::TableName)->select(SELF::ColumnEmail, $googleEmail);
    }

}