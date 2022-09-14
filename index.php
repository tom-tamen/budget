<?PHP
    include("connexion.php");
    include("initSession.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <title>Hoozpend</title>
</head>
<body class='indexBody'>
    <div class='main'>
        <h1>Hoozpend ?</h1>
        <?PHP
        $verif = $database->prepare('SELECT * FROM compte');
        $verif->execute();
        $existe = $verif->rowCount();
        $datas = $verif->fetchAll();

        if($existe == 0){
            ?><h2>Ajouter un compte</h2> 
            <div class='newaccount'>
                <p>Ajouter un nouveau compte :</p>
                <form action='index.php' name="newAccount"  method="post">
                    <div class='innerInput'>
                        <label for="newPrénom">Prénom</label>
                        <input type="text" name="newPrénom" id="newPrénom">
                    </div>
                    <div class='innerInput'>
                        <label for="newNom">Nom</label>
                        <input type="text" name="newNom" id="newNom">
                    </div>
                    <input type="submit" value="Ajouter un compte">
                </form>
            </div>
            <?PHP

        }else{
            if(!isset($_SESSION['id'])){
                ?><h2>Veuillez sélectionner un profil</h2>
                <form action="index.php" name="account"  method="post" id="account">
                    <select name="id" id="id">
                        <?PHP
                            foreach($datas as $data){
                                ?>
                                <option value="<?PHP echo $data["id"]?>"><?PHP echo $data["prénom"]?> <?PHP echo $data["nom"]?></option>
                                <?PHP
                            }
                        ?>
                    </select>
                    <input type="submit" value="Changer">
                </form>
                <div class='newaccount'>
                    <p>Ajouter un nouveau compte :</p>
                    <form action='index.php' name="newAccount"  method="post">
                        <div class='innerInput'>
                            <label for="newPrénom">Prénom</label>
                            <input type="text" name="newPrénom" id="newPrénom">
                        </div>
                        <div class='innerInput'>
                            <label for="newNom">Nom</label>
                            <input type="text" name="newNom" id="newNom">
                        </div>
                        <input type="submit" value="Ajouter un compte">
                    </form>
                </div>
                <?PHP
            }
            else{
                ?><h2>Bienvenu <?PHP echo $_SESSION["prénom"]?> <?PHP echo $_SESSION["nom"]?></h2>
                <div class='first'>
                    <div class='elm1'>
                        <div class='newaccount'>
                            <p>Ajouter un nouveau compte :</p>
                            <form action='index.php' name="newAccount"  method="post">
                                <div class='alignInput'>
                                    <div class='innerInput'>
                                        <label for="newPrénom">Prénom</label>
                                        <input type="text" name="newPrénom" id="newPrénom">
                                    </div>
                                    <div class='innerInput'>
                                        <label for="newNom">Nom</label>
                                        <input type="text" name="newNom" id="newNom">
                                    </div>
                                </div>

                                <input type="submit" value="Ajouter un compte">
                            </form>
                        </div>
                    </div>
                    <div class='elm1'>
                        <div class='changeAccount'>
                            <p>Changer de compte :</p>
                            <form action="index.php" name="account"  method="post" id="account">
                                <select name="id" id="id">
                                    <?PHP
                                        foreach($datas as $data){
                                            ?>
                                            <option value="<?PHP echo $data["id"]?>"><?PHP echo $data["prénom"]?> <?PHP echo $data["nom"]?></option>
                                            <?PHP
                                        }
                                    ?>
                                </select>
                                <input type="submit" value="Changer">
                            </form>
                        </div>
                    </div>
                </div>
                <div class='newDep'>
                    <p>Ajouter une dépense</p>
                    <form action="index.php" name="dépense"  method="post" id="account">
                        <div class='newDepFirst'>
                            <div class='innerInput'>
                                <label for="montant">Montant</label>
                                <input type="number" step="any" name="montant" id="montant">
                            </div>
                            <div class='innerInput'>
                                <label for="date">Date</label>
                                <input type="date" name="date" id="date">
                            </div>
                            <div class='innerInput'>
                                <label for="catégorie">Catégorie</label>
                                <select name="catégorie" id="catselect">
                                    <option value="new">Nouveau</option>
                                    <?PHP
                                        $getdep = $database->prepare('SELECT catégorie FROM dépense');
                                        $getdep->execute();
                                        $deps = $getdep->fetchAll();
                                        $addcats = [];
                                        foreach($deps as $dep){
                                            if(in_array($dep["catégorie"],$addcats)){
                                            }else{
                                                ?>
                                                <option value="<?PHP echo $dep["catégorie"]?>"><?PHP echo $dep["catégorie"]?></option>
                                                <?PHP
                                                array_push($addcats, $dep["catégorie"]);
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class='innerInput catnew'>
                                <label for="catnew">Nouvelle catégorie</label>
                                <input type="text" name="catnew" id="catnew">
                            </div>
                        </div>
                        <input class='ajouter' type="submit" value="Ajouter">
                    </form>
                </div>
                
            <?PHP
            }
        }
            ?>
            <a class='link' href="stats.php">Statistiques</a>

    </div>
    <?PHP
        if(isset($_SESSION['id']) && !empty($_SESSION['id'])){
            ?>
                <div class='printDep'>
                    <?PHP 
                    $req = $database->prepare('SELECT * FROM dépense WHERE compteID = :compteID ORDER BY dateDep DESC');
                    $req->execute(['compteID'=>$_SESSION['id']]);
                    $nbcolones = $req->rowCount();

                    @$page=$_GET["page"];
                    $pagem=$page-1;

                    if(empty($page)) $page=1;
                    $elmParPages=10;
                    $nbr_pages=ceil($nbcolones/$elmParPages);
                    $debut = ($page-1)*$elmParPages;
                    echo "<p class='nbrPages'>Nombre de pages : $nbr_pages</p>";?>
                    <table class='depsTable'>
                        <thead>
                            <tr>
                                <th colspan="2">Historique des dépenses pour <?PHP echo $_SESSION["prénom"]." ".$_SESSION["nom"]?></th>
                            </tr>
                        </thead>
                        <?PHP
                        
                        if($nbr_pages<1){
                            echo "<p>Aucun historique</p>"
                            ?></table><?php
                        }else{
                            ?>
                            <tbody>
                            <tr>
                                <td class='blue'>Montant</td>
                                <td class='blue'>Catégorie</td>
                                <td class='blue'>Date</td>
                            </tr>
                            <?PHP

                            $nreq = $database->prepare('SELECT * FROM dépense WHERE compteID = :compteID ORDER BY dateDep DESC LIMIT :debut, :fin');
                            $nreq->bindValue('compteID', $_SESSION['id'], PDO::PARAM_STR);
                            $nreq->bindValue('debut', $debut, PDO::PARAM_INT);
                            $nreq->bindValue('fin', $elmParPages, PDO::PARAM_INT);
                            $nreq->execute();
                            $ttdeps = $nreq->fetchAll();
                            if($page<=0 || $page>$nbr_pages){
                                header("Location: index.php");
                            }

                            ?>
                                <div class='pages'>
                                    <?PHP
                                    if($page <= 1){
                                        echo "<a class='linkPages down'><</a>&nbsp;";
                                    }else{
                                        echo "<a class='linkPages' href='?page=$pagem'><</a>&nbsp;";
                                    }
                                    if($page>=5){
                                        for($j=$page-4;$j<=$page;$j++){
                                            if($page!=$j){
                                                echo "<a class='linkPages' href='?page=$j'>$j</a>&nbsp;";
                                            }else{
                                                echo "<a class='linkPages afocus' >$j</a>&nbsp;";
                                            }
                                        }
                                    }else{
                                        for($i=1;$i<=$nbr_pages && $i<6 ;$i++){
                                            if($page!=$i){
                                                echo "<a class='linkPages' href='?page=$i'>$i</a>&nbsp;";
                                            }else{
                                                echo "<a class='linkPages afocus' >$i</a>&nbsp;";
                                            }
                                        }
                                    }
                                    if($page+1 <= $nbr_pages){
                                        ?><a class='linkPages' href='?page=<?PHP echo $page+1 ?>'>></a><?PHP
                                    }else{
                                        ?><a class='linkPages down'>></a><?PHP
                                    }
                                    ?>
                                    <form action="index.php" method='post'>
                                        <input class='linkPages exp' type='number' placeholder='...' name='PageN'>
                                        <input type="submit" class='linkPages exp' value="GO">
                                    </form>
                                </div>
                            <?PHP

                            foreach($ttdeps as $ttdep){
                                $color = "";
                                if($ttdep['montant']>0){
                                    $color = "green";
                                }else{
                                    $color = "red";
                                }
                                ?><tr>
                                    <td class="<?PHP echo $color?>"><?PHP echo $ttdep['montant']?></td>
                                    <td class="<?PHP echo $color?>"><?PHP echo $ttdep['catégorie'] ?></td>
                                    <td class="<?PHP echo $color?>"><?PHP echo $ttdep['dateDep']?></td>
                                </tr><?PHP
                            }
                            ?>
                        </tbody>
                    </table>
                        <?PHP
                        }
                        ?>
                </div>
            <?php
        }
    ?>
</body>
</html>

<?PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    if(isset($_POST["id"]) && !empty($_POST["id"])){
        $id = $_POST['id'];
        $verif = $database->prepare('SELECT * FROM compte WHERE id = :id');
        $verif->execute(['id'=>$id]);
        $newdata = $verif->fetch();
        $_SESSION["prénom"] = $newdata["prénom"];
        $_SESSION["nom"] = $newdata["nom"];
        $_SESSION["id"] = $newdata["id"];
        header("Location: index.php");
    }

    if(isset($_POST["montant"]) && !empty($_POST["montant"])){
        $montant = floatval($_POST["montant"]);
        $dateDep = $_POST["date"];
        $idCompte = intval($_SESSION['id']);
        if($_POST["catégorie"] == 'new'){
            $cat = $_POST["catnew"];
        }else{
            $cat = $_POST["catégorie"];
        }

        $newdep = $database->prepare('INSERT INTO dépense(catégorie, montant, dateDep, compteID) VALUES(:categorie, :montant, :dateDep, :compteID)');
        $newdep->execute(['categorie'=>$cat, 'montant'=>$montant, 'dateDep'=>$dateDep, 'compteID'=>$idCompte]);
        header("Location: index.php");
    }
    
    if(isset($_POST["newPrénom"]) && !empty($_POST["newPrénom"]) && isset($_POST["newNom"]) && !empty($_POST["newNom"])){
        $newAccount = $database->prepare('INSERT INTO compte(prénom, nom) VALUES(:prenom, :nom)');
        $newAccount->execute(['prenom'=>$_POST["newPrénom"], 'nom'=>$_POST["newNom"]]);
        header("Location: index.php");
    }

    if(isset($_POST['PageN']) && !empty($_POST['PageN'])){
        if($_POST['PageN']<=0){}
        else header('Location: index.php?page='.$_POST['PageN']);
    }
}



?>
