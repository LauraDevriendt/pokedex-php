<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);

if (filter_has_var(INPUT_GET, 'submit')) {
    $pokemon = strtolower($_GET['name']);
    if (!empty($pokemon)) {

        $jsonGeneralData = file_get_contents("https://pokeapi.co/api/v2/pokemon/$pokemon", true);
        $generalData = json_decode($jsonGeneralData, true);
        $pokeFront = $generalData['sprites']['front_shiny'];
        $pokeBack = $generalData['sprites']['back_shiny'];
        $id = $generalData['id'];

        $jsonPokemonData = file_get_contents("https://pokeapi.co/api/v2/pokemon-species/$id", true);
        $pokemonData = json_decode($jsonPokemonData, true);

        $evolutionArr = getEvolutions($pokemonData);

        // find position current pokemon
        $pos = array_search($generalData['name'], $evolutionArr, true);

        // check prev and next evo
        if ($pos - 1 >= 0) {
            --$pos;
            $prevEvoImg = getEvoImg($pos, $evolutionArr);
        }

        if ($pos + 1 < count($evolutionArr)) {
            ++$pos;
            $nextEvoImg = getEvoImg($pos, $evolutionArr);
        }

        // moves
        $moves = getMoves($generalData);


    }
}

function getEvolutions(array $pokemonData): array
{
    $jsonEvoData = file_get_contents($pokemonData['evolution_chain']['url'], true);
    $evoData = json_decode($jsonEvoData, true);
    $path = $evoData['chain'];
    $evolutionArr = array($path['species']['name']);
    $evolutionArr = getOtherEvo($path, $evolutionArr);
    return $evolutionArr;
}

function getOtherEvo(array $path, array $evolutionArr): array
{

    while (count($path['evolves_to']) > 0) {
        foreach ($path['evolves_to'] as $evo) {
            $evolutionArr[] = $evo['species']['name'];
        }
        $path = $path['evolves_to'][0];
    }
    return $evolutionArr;
}

function getEvoImg(int $pos, array $evolutionArr): string
{
    $prevEvoName = $evolutionArr[$pos];
    $jsonPrevEvo = file_get_contents("https://pokeapi.co/api/v2/pokemon/$prevEvoName", true);
    $prevEvo = json_decode($jsonPrevEvo, true);
    return (string)$prevEvo['sprites']['front_shiny'];


}

function getMoves(array $generalData): array
{
    $moves = [];
    $movesData = $generalData['moves'];
    $randIndex = (array)array_rand($movesData, min(4, count($movesData)));
    foreach ($randIndex as $index) {
        $moves[] = $movesData[$index]['move']['name'];
    }
    return $moves;

}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/2e0a884014.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=BenchNine:wght@300&family=Orbitron&display=swap"
          rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
          integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <title>Pokedex</title>
</head>
<body id="pokedex">
<div class="container-fluid px-0">
    <header>
        <div class="headerH1">
            <h1>Pokemon</h1>
        </div>
    </header>
    <nav class="navbar navbar-light bg-light">
        <a class="nav-link" href="index.php">Pokedex</a>
        <a class="nav-link active" href="category.php">Categories</a>
    </nav>
    <main class="container">
        <div class="pokedexCtn">
            <img src="img/pokedexpaint.png" alt="pokedex">
            <div id="pokemontypecolor" class="pokemonImgCtn
            <?php
            if (!empty($generalData)) {
                foreach ($generalData['types'] as $type) {
                    $type = $type['type']['name'];
                    echo "$type ";
                }
            }
            ?>">
                <div class="row">
                    <div class="pokemonFrontImg text-center"><?php if (!empty($pokeFront)) echo "<img src=$pokeFront alt='pokeFront'>" ?></div>
                    <div class="pokemonBackImg text-center"><?php if (!empty($pokeBack)) echo "<img src=$pokeBack alt='pokeBack'>" ?></div>
                </div>
                <div class="row typesCont">
                    <p class="typeHeading ml-2 text-white"><?php
                        if (!empty($generalData)) {
                            echo 'Type: ';
                        } ?></p>&nbsp;
                    <div class="types text-white">
                        <?php
                        if (!empty($generalData)) {
                            foreach ($generalData['types'] as $key => $type) {
                                if ($key === 0) {
                                    $firsttype = $type['type']['name'];
                                    echo "<p>$firsttype</p>";
                                } else {
                                    $typeOther = $type['type']['name'];
                                    echo "<p> - $typeOther</p>";
                                }

                            }
                        }


                        ?></div>
                </div>

            </div>
            <div class="idBtnCtn">
                <a href=<?php if ($id - 1 >= 0) echo "?name=" . ($id - 1) . "&submit="; ?>>
                    <button id="prevId" title="Previous ID" class="btn btn-success idBtnStyle">
                        <i class="fas fa-backward"></i>
                    </button>
                </a>
                <a href=<?php echo "?name=" . ($id + 1) . "&submit="; ?>>
                    <button id="nextId" title="Next ID" class="btn btn-warning idBtnStyle">
                        <i class="fas fa-forward"></i>
                    </button>
                </a>
            </div>
            <div class="idScreenCtn"><?php if (!empty($generalData)) echo $id; ?></div>
            <div class="nameAndMovesCtn">
                <h5 class="name"><?php if (!empty($generalData)) echo strtoupper($generalData['name']); ?></h5>
                <ul>
                    <div class="row">
                        <li class="col pokeMove"><?php if (!empty($moves)) echo $moves[0]; ?></li>
                        <li class="col pokeMove"><?php if (!empty($moves)) echo $moves[1]; ?></li>
                    </div>
                    <div class="row">
                        <li class="col pokeMove"><?php if (!empty($moves)) echo $moves[2]; ?></li>
                        <li class="col pokeMove"><?php if (!empty($moves)) echo $moves[3]; ?></li>
                    </div>
                </ul>
            </div>
            <div class="warningCtn">
                <p>Fetch error or invalid name/id!</p>
            </div>
            <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="searchCtn">
                <input type="text" name="name" class="form-control"
                       value="<?php echo isset($_GET['name']) ? $pokemon : ""; ?>">
                <button id="run" class="btn btn-secondary" name="submit" type="submit"><i class="fas fa-search"></i>
                </button>
            </form>
            <div class="evolutionImgCtn">
                <div title="Previous evolution"
                     class="prevEvo text-center"><?php if (!empty($prevEvoImg)) echo "<a href=" . "?name=" . ($id - 1) . "&submit=>" . "<img src=$prevEvoImg alt='prevEvo'></a>"; ?></div>
                <div title="Next evolution"
                     class="nextEvo text-center"><?php if (!empty($nextEvoImg)) echo "<a href=" . "?name=" . ($id + 1) . "&submit=>" . "<img src=$nextEvoImg alt='nextEvo'></a>"; ?></div>

            </div>
        </div>
    </main>
    <footer>
        &copy; Copyright 2020.
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
</body>
</html>