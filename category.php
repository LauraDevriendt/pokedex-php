<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);
$jsondata = file_get_contents("https://pokeapi.co/api/v2/pokemon?limit=964", true);
$data = json_decode($jsondata, true);
$urls = [];
$types=[];
$currentpage=(isset($_GET['page']))? (int)($_GET['page']):1;
$displayNumber=20;


    foreach (array_slice($data['results'], ($currentpage-1)*$displayNumber,$displayNumber) as $pokemon) {
        $name = $pokemon['name'];
        $url = $pokemon['url'];
        $urls[] = $url;

    }







if (filter_has_var(INPUT_POST, 'submit')) {
// get form data
$displayNumber= $_POST['displaynumbers'];
}
if (filter_has_var(INPUT_GET, 'submit')) {
// get form data
    $jsontypeData= file_get_contents("https://pokeapi.co/api/v2/type/".$_GET['types'],true);
    $typeData=json_decode($jsontypeData,true);



}






class PokemonsInfo
{
    private $id;
    private $name;
    private $frontImg;

    function __construct($id, $name, $frontImg)
    {
        $this->id = $id;
        $this->name = $name;
        $this->frontImg = $frontImg;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFrontImg()
    {
        return $this->frontImg;
    }
}







?>
<!doctype html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
          integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <title>Pokédex</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="">
    <div class="headerH1">
        <h1>Pokemon</h1>
    </div>
</header>
<nav class="navbar navbar-light bg-light">
    <a class="nav-link" href="index.php">Pokedex</a>
    <a class="nav-link active" href="category.php">Categories</a>
</nav>
<section id="filters" class="mt-2">
    <form method="post" class="my-2">
        <div class="input-group">
            <div class="input-group-append">
                <button name="submit" type="submit" class="btn btn-danger">Set displayNumber</button>
            </div>
            <select name="displaynumbers" id="displaynumbers">
                <option value="20">20</option>
                <option value="40">40</option>
                <option value="60">60</option>
            </select>
        </div>
    </form>
    <form method="get" class="my-2">
        <div class="input-group">
            <div class="input-group-append">
                <button name="submit" type="submit" class="btn btn-danger">Set type</button>
            </div>
            <select name="types" id="types">
                <?php
                $types = json_decode(file_get_contents("https://pokeapi.co/api/v2/type", true), true);
                foreach ($types['results'] as $type) {
                    echo "<option value=".$type['name'].">".$type['name']."</option>";
                }

                ?>
            </select>
        </div>
    </form>


</section>
<section id="paginationAbove" class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=1" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?php
                if($currentpage!==1){
                    echo $currentpage-1;
                } else{
                    echo 1;
                } ?>" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?=$currentpage?>"><?= $currentpage ?></a></li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?php
                if($currentpage!==ceil(count($data['results'])/$displayNumber)){
                   echo $currentpage+1;
                }else{
                    echo $currentpage;
                }


                ?>" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?=ceil(count($data['results'])/$displayNumber) ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
</section>
<section id="favorites">

</section>

<section id="pokemons" class="container">
    <div id="pokemonsRow" class="row d-flex justify-content-center">
        <?php

        $pokemons=[];
        foreach ($urls as $key => $url) {
            $jsondataPokemon = file_get_contents($url, true);
            $dataPokemon = json_decode($jsondataPokemon, true);

            $pokemons[]=new PokemonsInfo($dataPokemon['id'],  $dataPokemon['name'], $dataPokemon['sprites']['front_shiny']);
        }




        if(!empty($pokemons)){

            foreach ($pokemons as $pokemon){
                echo  '<div class="pokemon mr-3 my-2 card col-5">
  <img class="pokemonImg card-img-top" src="'.$pokemon ->getFrontImg().'" alt="pokemon">
  <div class="card-body">
    <h4 class="card-title text-center">'.$pokemon ->getId().': '.ucwords($pokemon ->getName()).'</h4>
    <div class="text-center"><a href="#!" class="btn btn-warning">More information</a></div>
  </div>
</div>';

            }
        }


        ?>
    </div>

</section>
<section id="paginationAbove" class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=1" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?php
                if($currentpage!==1){
                    echo $currentpage-1;
                } else{
                    echo 1;
                } ?>" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?=$currentpage?>"><?= $currentpage ?></a></li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?php
                //@todo NOT WORKING FIX CEILING
                if($currentpage!==ceil(count($data['results'])/$displayNumber)){
                    echo $currentpage+1;
                }else{
                    echo ceil(count($data['results'])/$displayNumber);
                }


                ?>" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="http://becode.local/pokedex-php/category.php?page=<?=ceil(count($data['results'])/$displayNumber) ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
</section>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
<!-- Customised script -->
<script src="resources/js/script.js"></script>
</body>
</body>
</html>