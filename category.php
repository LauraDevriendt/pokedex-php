<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);


$jsondata = file_get_contents("https://pokeapi.co/api/v2/pokemon?limit=964", true);
$data = json_decode($jsondata, true);
$urls = [];
$typeData = [];
$currentpage = (isset($_GET['page'])) ? (int)($_GET['page']) : 1;
$typefilter = false;


if (isset($_POST['displaynumbers'])) {
    $_COOKIE['displaynumber'] = $_POST['displaynumbers'];
    setcookie('displaynumber', $_POST['displaynumbers'], time() + (86400 * 30));
}

$displayNumber = (isset($_COOKIE['displaynumber'])) ? (int)($_COOKIE['displaynumber']) : 20;


foreach (array_slice($data['results'], ($currentpage - 1) * $displayNumber, $displayNumber) as $pokemon) {
    $url = $pokemon['url'];
    $urls[] = $url;
}


$pokemons = [];
function makePokemons($urls)
{
    foreach ($urls as $key => $url) {
        $jsondataPokemon = file_get_contents($url, true);
        $dataPokemon = json_decode($jsondataPokemon, true);

        $pokemons[] = new PokemonsInfo($dataPokemon['id'], $dataPokemon['name'], $dataPokemon['sprites']['front_shiny']);
    }
    return $pokemons;
}

$pokemons = makePokemons($urls);


if (filter_has_var(INPUT_GET, 'submit')) {
    $typefilter = true;

    $jsontypeData = file_get_contents("https://pokeapi.co/api/v2/type/" . $_GET['types'], true);
    $typeData = json_decode($jsontypeData, true);
    $urls = [];
    foreach (array_slice($typeData['pokemon'], ($currentpage - 1) * $displayNumber, $displayNumber) as $pokemon) {
        $urls [] = $pokemon['pokemon']['url'];

    }
    $pokemons = makePokemons($urls);


}

function previousPage($typefilter, $currentpage)
{
    if ($typefilter) {

        if ($currentpage !== 1) {
            $currentpage = $currentpage - 1;
            return "category.php?submit=&types=" . $_GET['types'] . "&page=$currentpage";
        } else {
            $currentpage = 1;
            return "category.php?submit=&types=" . $_GET['types'] . "&page=$currentpage";
        }

    } else {
        if ($currentpage !== 1) {
            $currentpage = $currentpage - 1;
            return "category.php?page=$currentpage";
        } else {
            return "category.php?page=1";
        }
    }


}

function nextPage($typefilter, $currentpage, $typedata, $data, $displayNumber)
{
    if ($typefilter) {

        if ($currentpage < ceil(count($typedata['pokemon']) / $displayNumber)) {
            ++$currentpage;
            echo "category.php?submit=&types=" . $_GET['types'] . "&page=$currentpage";
        } else {

            echo "category.php?submit=&types=" . $_GET['types'] . "&page=$currentpage";
        }

    } else {

        if ($currentpage < ceil(count($data['results']) / $displayNumber)) {
            ++$currentpage;
            echo "category.php?page=$currentpage";
        } else {
            echo "category.php?page=$currentpage";
        }
    }

}


$favourites = [];

if (isset($_COOKIE['favourite'])) {

    $favourites = array_unique(json_decode($_COOKIE['favourite']));

    if(isset($_GET['noFavourite'])){

        $key= array_search($_GET['noFavourite'], $favourites, true);
        unset($favourites[$key]);



    }
}

if (isset($_GET['favourite'])) {


    $favourites[] = $_GET['favourite'];

    $_COOKIE['favourite'] = json_encode(array_unique($favourites));
    setcookie('favourite', json_encode(array_unique($favourites), JSON_THROW_ON_ERROR));
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
                    echo "<option value=" . $type['name'] . ">" . $type['name'] . "</option>";
                }

                ?>
            </select>
        </div>
    </form>


</section>
<section id="favorites">
    <h1 class="text-center">Favourites</h1>
    <div class="container">
        <div class="row">
            <?php

            if(!empty($_COOKIE['favourite'])){
                print_r(json_decode($_COOKIE['favourite']));
                foreach (json_decode($_COOKIE['favourite']) as $favourite){
                    findObjectById($favourite,$pokemons,$currentpage);

                }
            }
            function findObjectById($favourite,$pokemons){


                foreach ( $pokemons as $pokemon ) {
                    if ( $favourite == $pokemon->getId() ) {
                        echo '<div class="pokemon mr-3 my-2 card col-2">
  <img class="fluid-img pokemonImg card-img-top" src="' . $pokemon->getFrontImg() . '" alt="pokemon">
  <div class="card-body">
    <h6 class="card-title text-center">' . $pokemon->getId() . ': ' . ucwords($pokemon->getName()) . '</h6>
    <div class="text-center"><a href="index.php?name=' . $pokemon->getId() . '&submit=" class="btn btn-warning mr-3">More information</a><a  title="delete favourite" class="unheart" href="category.php?' . '&noFavourite=' . $pokemon->getId() . '"><i class="far fa-heart"></i> Unheart</a></div>
  </div>
</div>';
                    }
                }


            }

            ?>
        </div>
    </div>

</section>
<section id="paginationAbove" class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href=<?php
                if ($typefilter) {
                    echo "&types=" . $_GET['types'] . "&page=1";
                } else {
                    echo "category.php?&page=1";
                }
                ?> aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= previousPage($typefilter, $currentpage)

                ?>" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link"
                                     href="/category.php?page=<?= $currentpage ?>"><?= $currentpage ?></a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= nextPage($typefilter, $currentpage, $typeData, $data, $displayNumber)
                ?>" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link"
                   href="<?php
                   if ($typefilter) {
                       echo "category.php?submit=&types=" . $_GET['types'] . "&page=" . ceil(count($typeData['pokemon']) / $displayNumber);
                   } else {
                       echo "category.php?page=" . ceil(count($data['results']) / $displayNumber);
                   }
                   ?>"
                   aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
</section>
<section id="pokemons" class="container">
    <div id="pokemonsRow" class="row d-flex justify-content-center">
        <?php


        if (!empty($pokemons)) {

            foreach ($pokemons as $pokemon) {
                echo '<div class="pokemon mr-3 my-2 card col-5">
  <img class="pokemonImg card-img-top" src="' . $pokemon->getFrontImg() . '" alt="pokemon">
  <div class="card-body">
    <h4 class="card-title text-center">' . $pokemon->getId() . ': ' . ucwords($pokemon->getName()) . '</h4>
    <div class="text-center"><a href="index.php?name=' . $pokemon->getId() . '&submit=" class="btn btn-warning mr-3">More information</a><a  title="Add to favorites" class="heart" href="category.php?page=' . $currentpage . '&favourite=' . $pokemon->getId() . '"><i class="far fa-heart"></i> heart it</a></div>
  </div>
</div>';

            }
        }


        ?>
    </div>

</section>
<section id="paginationDown" class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href=<?php
                if ($typefilter) {
                    echo "category.php?submit=&types=" . $_GET['types'] . "&page=1";
                } else {
                    echo "category.php?page=1";
                }
                ?> aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= previousPage($typefilter, $currentpage)

                ?>" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link"
                                     href="category.php?page=<?= $currentpage ?>"><?= $currentpage ?></a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= nextPage($typefilter, $currentpage, $typeData, $data, $displayNumber)
                ?>" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link"
                   href="<?php
                   if ($typefilter) {
                       echo "category.php?submit=&types=" . $_GET['types'] . "&page=" . ceil(count($typeData['pokemon']) / $displayNumber);
                   } else {
                       echo "category.php?page=" . ceil(count($data['results']) / $displayNumber);
                   }
                   ?>"
                   aria-label="Next">
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
<script src="https://kit.fontawesome.com/1cf94312e8.js" crossorigin="anonymous"></script>
<!-- Customised script -->
<script src="resources/js/script.js"></script>
</body>
</body>
</html>