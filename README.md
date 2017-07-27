# Secure-php-login-system

This is a PHP based lightweight, object oriented design login system. It is very simple and easy to integrate to any application and very secure.

**Features**

Add new User

Persistent Cookies

Password Recovery

Bcrypt Hashing Algorthim 

Separate Database Class


**Usage**

Index.php has a detailed example of the usage but here are some of them;

The classes use an autoloader which is included in the config.php so you don't need to include or require any file. However, you have to include
Config.php in every file for the autloader to work and the easy and best way is to use an .htaccess to do this and I have also
included .htaccess file which does that. If you have access to php.ini file you could also add it in there.

Simply download the zip and extract the files to your root directory and you are good to go.

To Login
<code>

    use User\User;
    use User\UserError;
    
    $user = new User;
    try{
        //the third parameter is optional remember me feature; if you don't want it remove it;
        $user->login("test@mail.com", "my_password", 1);
    }
    catch(UserError $e){
        echo $e->getMessage();
    }
</code>

**Database Class**

This repo also includes a database class which is very easy to use so you can simply add your database and start using the class.

Usage of the database class looks like this and it supports both pdo and mysqli; look inside index.php for more details

<code>
    
    use Config\Database;

    $db = new Database(01);
    $stmt = $db->query("select * from your_table where name = ? and email = ?", [$name, $email]);
    foreach($stmt as $row){
        echo $row->phone;
    }
</code>
