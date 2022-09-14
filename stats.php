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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">    
    <title>Statistiques</title>
</head>
<body>
    <main>
        <h1>Statistiques</h1>
        <?PHP
        $GetUsers = $database->prepare('SELECT * FROM compte');
        $GetUsers->execute();
        $Users = $GetUsers->fetchAll();
        $res = [];
        foreach($Users as $User){
            ?>
            <div class='user'>
                <p><?PHP echo $User['prénom']." ".$User['nom'] ?></p>
                <?PHP
                $fday = date('Y-m')."-01";
                $lday = date("Y-m-t", strtotime($fday));
                array_push($res, calcul_part($User['prénom'], $User['nom'], $User['id'], $fday ,$lday, false)) ?>
            </div>
            
            <?PHP
        }
        $label=[];
        foreach($res as $resultat){
            array_push($label, $resultat['prénom']." ".$resultat['nom']);
        }
        ?>
        <div class="overtotal">
            <p>TOUT LE MONDE</p>
            <?PHP
                // calcul_part($User['prénom'],$User['nom'], $User['id'], $fday ,$lday, true)
                /**?><p><?PHP echo $res[1]['dépenseCat']['pain']?></p><?PHP**/
                total_part($res);
            ?>
        </div>
        <div class='graph'>
            <div>
                <canvas id="myChart"></canvas>
            </div>
        </div>
        <form action="stats.php" method='post'>
            <input type="date" name="Fdate" id="Fdate">
            <input type="date" name="Sdate" id="Sdate">
            <input type="submit" value="Charger">
        </form>

        <a class='link' href="index.php">Accueil</a>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <script src="statistiques.js"></script> -->
    <script>
    let labels=<?php echo json_encode($label)?>
    
    const data = {
        labels: labels,
        datasets: [{
            label: 'My First Dataset',
            data: [300, 50, 100],
            backgroundColor: [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)'
            ],
            hoverOffset: 4
        }]
    };

    const config = {
        type: 'doughnut',
        data: data,
    };

    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
    </script>
</body>
</html>











<?PHP
function calcul_part($prénom, $nom, $ID, $Fdate, $Sdate, $all){
    include("connexion.php");
    if($all){
        $req = $database->prepare('SELECT * FROM dépense WHERE dateDep BETWEEN :Fdate AND :Sdate');
        $req->execute(['Fdate'=>$Fdate,'Sdate'=>$Sdate]);
    }else{
        $req = $database->prepare('SELECT * FROM dépense WHERE compteID = :ID AND dateDep BETWEEN :Fdate AND :Sdate');
        $req->execute(['ID'=>$ID,'Fdate'=>$Fdate,'Sdate'=>$Sdate]);
    }
    $ttdeps = $req->fetchAll();
    $totalCatVu =[];
    $totalCatMoney =[];
    $salaire=0;
    foreach($ttdeps as $ttdep){
        if($ttdep["montant"]>0){
            $salaire+=floatval($ttdep["montant"]);
        }else if(in_array($ttdep["catégorie"],$totalCatVu)){
            $totalCatVu[$ttdep["catégorie"]]+=1;
            $totalCatMoney[$ttdep["catégorie"]]+=floatval($ttdep["montant"]);
        }else{
            array_push($totalCatVu, $ttdep["catégorie"]);
            $totalCatVu[$ttdep["catégorie"]]=1;
            array_push($totalCatMoney, $ttdep["catégorie"]);
            $totalCatMoney[$ttdep["catégorie"]]=floatval($ttdep["montant"]);
        }
    }
    // var_dump($totalCatMoney['pain']."          |          ".$totalCatVu['pain']);
    $totaldep=0;
    foreach($totalCatVu as $key => $value){
        $totaldep+=intval($value);
    }
    $totalMoney=0;
    foreach($totalCatMoney as $key => $value){
        $totalMoney+=floatval($value);
    }
    // var_dump($totalMoney);
    $totalPourcentage=calcul_pourcentage($totalCatMoney,$totalMoney);
    ?><h2><?PHP echo "Solde restant : ".number_format($totalMoney+$salaire,2)." €"?></h2><?PHP
    ?><h2><?PHP echo " Total dépensé : ".number_format($totalMoney,2)." €"?></h2><?PHP
    
    return ["totalRestant"=> $totalMoney+$salaire,
            "totalDépensé" => $totalMoney,
            "pourcentage"=> $totalPourcentage,
            "dépenseCat" => $totalCatMoney,
            "nom" => $nom,
            "prénom"=> $prénom,
            "UserID"=>$ID];
}

function calcul_pourcentage($totalCatMoney,$totalMoney){
    $totalPourcentage = [];
    foreach($totalCatMoney as $key => $value){
        if(gettype($value)=="string"){
        }else{
            $totalPourcentage[$key]=number_format(floatval($value)*100/$totalMoney,2)."%";
            ?><p><?PHP echo $key." : ".$totalPourcentage[$key]." | ".$value." €"; ?></p><?PHP
        }
    }
    return $totalPourcentage;
}

function total_part($resultats){
    $fulltotalrest=0;
    $fulltotaldep=0;
    $FullTotalMoneyVu = [];
    $FullTotalCatMoney = [];
    foreach($resultats as $res){
        $fulltotalrest+=$res["totalRestant"];
        $fulltotaldep+=$res["totalDépensé"];
        foreach($res["dépenseCat"] as $key => $value){
            // echo $key." | ".$value." | ";
            
            if(gettype($value)=="string"){
                unset($res[$key]);
            }
            else if(in_array($key, $FullTotalMoneyVu)){
                $FullTotalCatMoney[$key] += $value;
            }else{
                array_push($FullTotalMoneyVu,$key);
                $FullTotalCatMoney[$key] = $value;
            }
        }
        // var_dump($FullTotalCatMoney);
        // echo($FullTotalCatMoney['pain']." | ");
    }

    $totalPourcentage = calcul_pourcentage($FullTotalCatMoney,$fulltotaldep);

    foreach($resultats as $res){
        foreach($res["dépenseCat"] as $key => $value){
            if(gettype($value)=="string"){
                unset($res[$key]);
            }else{
                echo $res["prénom"]." ".number_format(floatval($value)*100/$FullTotalCatMoney[$key],2)." % de ".$key." | ";
            }
        }
    }

    ?><h2><?PHP echo "Solde restant : ".$fulltotalrest." €"?></h2><?PHP
    ?><h2><?PHP echo " Total dépensé : ".$fulltotaldep." €"?></h2><?PHP
    // var_dump($FullTotalCatMoney);
    // echo($FullTotalCatMoney['pain']." | ");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST["Fdate"]) && !empty($_POST["Fdate"]) && isset($_POST["Sdate"]) && !empty($_POST["Sdate"])){
        $FirstDate = $_POST["Fdate"];
        $SecondeDate = $_POST["Sdate"];
        calcul_part($User['prénom'], $User['nom'], $User['id'], $FirstDate ,$SecondeDate, false);
    }
}
?>