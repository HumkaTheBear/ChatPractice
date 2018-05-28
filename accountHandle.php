<?php
    $receiveMsg = file_get_contents("php://input");
    $receive = json_decode($receiveMsg);

    $servername = 'localhost';
	$username = 'root';
	$password = '';
	$dbname = 'chat';


    if (isset($_REQUEST['submit'])) {
        try{
            // $_REQUEST['password'] = hash('md2', $_REQUEST['password']);

			$connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$stmt = $connection->prepare("
                INSERT INTO `users`
                    (`username`, `password`, `firstname`, `lastname`, `birthday`, `email`)
                VALUES
                    (:un, :pw, :firn, :famn, :birthday, :email);");
            $cre = strtolower($_REQUEST['username']);
            $stmt->bindParam(':un', $cre);
            $stmt->bindParam(':pw', $_REQUEST['password']);
            $stmt->bindParam(':firn', $_REQUEST['firstName']);
            $stmt->bindParam(':famn', $_REQUEST['lastName']);
            $stmt->bindParam(':birthday', $_REQUEST['birthDay']);
            $stmt->bindParam(':email', $_REQUEST['email']);

            $stmt->execute();

            $connection = null;
            $stmt = null;
            echo'
            <!DOCTYPE html>
            <html lang="en" dir="ltr">
                <head>
                    <meta charset="utf-8">
                    <title></title>
                </head>
                <body style="text-align: center">
                    Account created!<br> You will be redirected in <span id="countdown">5</span>seconds!<br>
                    If it didn\'t happened click <a href="CreateAcc.html">here</a>
                    <script>
                    var timeleft = 5;
                    var downloadTimer = setInterval(function(){
                        timeleft--;
                        document.querySelector("#countdown").textContent = timeleft;
                        if(timeleft <= 0)
                            clearInterval(downloadTimer);
                        },1000);
                        setTimeout(function () {window.location.href= "Welcome.html"},5000);
                    </script>
                </body>
            </html>';
        }
        catch(PDOException $e) {
        }
    }
    elseif (isset($receive->loginname)) {
        $userlog = $receive->loginname;
        $userlog = strtolower($userlog);
        $userpass = $receive->loginpass;
        try{
			$connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$stmt = $connection->prepare("
                SELECT `username`, `password`
                FROM `users`
                WHERE `username`= :un;");
            $stmt->bindParam(':un', $userlog);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($result['username'])) {
                if ($result['username']===$userlog && $result['password']===$userpass) {
                    session_start();
                    $_SESSION['username'] = $result['username'];
                    $_SESSION['password'] = $result['password'];
                }
                else{
                    echo 'Wrong Password or Username! Try again!';
                }
            }
            else {
                echo 'Wrong Password or Username! Try again!';
            }

            $connection = null;
            $stmt = null;
        }
        catch(PDOException $e) {
        }
    }

    elseif (isset($receive->access)) {
        session_start();
        if (isset($_SESSION['username'])){
            echo($_SESSION['username']);
        }
        else {

        }
    }
    elseif (isset($receive->delete)) {
        session_start();
        session_destroy();
    }
    elseif (isset($receive->message)){
        try{
            session_start();
			$connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$stmt = $connection->prepare("
                INSERT INTO `messages`
                    (`from_user`, `to_user`, `message`)
                VALUES
                    (:from, :to, :message);");
            $stmt->bindParam(':from', $_SESSION['username']);
            $stmt->bindParam(':to', $receive->whoto);
            $stmt->bindParam(':message', $receive->message);

            $stmt->execute();


            $connection = null;
            $stmt = null;
        }
        catch(PDOException $e) {
        }
    }
    elseif(isset($_REQUEST['usercheck'])) {
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$stmt = $connection->prepare("
                SELECT `username`
                FROM `users`
                WHERE `username`= :un;");
            $userch = strtolower($_REQUEST['usercheck']);
            $stmt->bindParam(':un', $userch);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if(isset($result['username'])){
                echo'exists';
            }
            $connection = null;
            $stmt = null;

        }
        catch (PDOException $e) {
        }
    }
    elseif(isset($receive->lastid)){
        $lastidint = $receive->lastid;
        $lastidint = str_replace("\"", "", $lastidint);
        $lastidint = intval($lastidint);
        try{
            session_start();
			$connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$stmt = $connection->prepare("
                SELECT `id`, `from_user`, `to_user`, `message`
                FROM `messages`
                WHERE (`id` > :where AND `from_user` = :from)
                    OR (`id` > :where AND `to_user` = :to)
                    OR (`id` > :where AND `to_user` = 'all');");
            $stmt->bindParam(':where', $lastidint);
            $stmt->bindParam(':from', $_SESSION['username']);
            $stmt->bindParam(':to', $_SESSION['username']);

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = json_encode($result);
            echo($result);

            $connection = null;
            $stmt = null;
        }
        catch(PDOException $e) {
        }
    }
?>
