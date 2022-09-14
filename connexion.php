<?php
try{
        $database = new PDO('mysql:host=localhost;dbname=budget','root','root');    
    } catch(PDOExeption $e) {
        die('Site indisponible');
    }
?>