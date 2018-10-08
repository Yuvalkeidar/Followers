<?php
$f3 = require ("fatfree/lib/base.php");
$f3->set('AUTOLOAD','API/,extenstion/');

$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=dbTest',
    'yuval',
    '123456789'
);

$f3->set('DB', $db);

$f3->route('GET /',

    function($f3) {
        // Instantiates a View object
        $view = new View;
        //RENDER screen according to cookies
        if(isset($_COOKIE['user_id'])){

            $db = $f3->get('DB');

            //fetch data from tables as login user
            $data = $db->exec(
                'SELECT     u.name                  as user_name, 
                            u.id                    as user_id,
                            u.follow_num            as follow_num,
                            g.name                  as group_name,
                            f.follower_id           as follower_id
                FROM        users     as u 
                JOIN        groups    as g  on g.id      = u.group_id 
                LEFT JOIN   followers as f  on f.user_id = u.id and f.follower_id = ?
                WHERE       u.id != ?
                ORDER BY    u.id',array($_COOKIE['user_id'],$_COOKIE['user_id'])

            );

            //fetch Username
            $username = $db->exec(
                'SELECT     u.name                  as user_name
                FROM        users     as u 
                WHERE       u.id=?',$_COOKIE['user_id']

            );
            // Page view
            echo $view->render('views/users.phtml','text/html',array('data' => $data,'username' => $username[0] ));
        }else{
            // Page view
            echo $view->render('views/NonLogIn.phtml','text/html');
            
        }
    }
);

//add follower request using transaction to avoid uncorrect data
$f3->route('POST /addFollow',

    function($f3,$params) {
            // This is a variable that we want to pass to the view
        $db = $f3->get('DB');               
        $follower_id = (isset($_COOKIE['user_id'])) ? $_COOKIE['user_id'] : "";
        $user_id     = (isset($f3['POST']['user_id'])) ? $f3['POST']['user_id'] : "";
        $follow_num  = (isset($f3['POST']['follow_num'])) ? $f3['POST']['follow_num'] : "";
        if($follower_id && $user_id){
            try {
                $db->begin();
                $db->exec('INSERT INTO followers VALUES (?,?)',array($user_id,$follower_id));
                $db->exec('UPDATE users SET follow_num=follow_num+1 WHERE id=?',array($user_id));
                $db->commit();
            } catch(\PDOException $e) {
                $f3->error(401, "Error occuered on insert to database");
            }
        }

    }
);

//delete follower request using transaction to avoid uncorrect data
$f3->route('POST /deleteFollow',

    function($f3,$params) {
        // This is a variable that we want to pass to the view
        $db = $f3->get('DB');            
        $follower_id = (isset($_COOKIE['user_id'])) ? $_COOKIE['user_id'] : "";
        $user_id     = (isset($f3['POST']['user_id'])) ? $f3['POST']['user_id'] : "";
        $follow_num  = (isset($f3['POST']['follow_num'])) ? $f3['POST']['follow_num'] : "";
        if($follower_id && $user_id){
            try {
                $db->begin();
                $db->exec('DELETE FROM followers WHERE user_id=? and follower_id=?',array($user_id,$follower_id));
                $db->exec('UPDATE users SET follow_num=follow_num-1 WHERE id=?',array($user_id));
                $db->commit();
            } catch(\PDOException $e) {
                $f3->error(401, "Error occuered on insert to database");
            }
        }
    }
);

$f3->run();
