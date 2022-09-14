<?php
if(!session_id()){
    session_start();
    session_regenerate_id();
}
?>