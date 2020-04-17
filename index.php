<?php
require 'vendor/autoload.php';

function obtenerElastic($campos) {

    $index = "/bank";
    $hosts = [
        'http://es01:9200'.$index,
        'http://es02:9200'.$index,
        'http://es03:9200'.$index
    ];
    $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

    if(empty($campos)) {
        $searchParams['body'] = array(
            'size' => 1,
            'query' => array(
                'match_all' => new \stdClass()
            )
        );
    } else {
        $busqueda = array();
        foreach ($campos as $nombre => $valor) {
            if($valor != '') {
                $busqueda[] = array('match' => array($nombre => $valor));
            }
        }

        $searchParams['body'] = array(
            'query' => array(
                'bool' => array(
                    "should" => $busqueda,
                    "minimum_should_match" => 1
                )
            )
        );
//        var_dump($searchParams['body']['query']['bool']['should']); die();
    }

    try {
        $resultado = $client->search($searchParams);
        return $resultado['hits']['hits'];
    } catch (Elasticsearch\Common\Exceptions\TransportException $e) {
        $previous = $e->getPrevious();
    }
}

$resultado = obtenerElastic($_POST);

?>

<form method="POST">
    <?php
    foreach ($resultado[0]['_source'] as $source => $value) {
        ?>
        <div>
            <label><?=$source?></label>
            <input type="text" name="<?=$source?>" value="<?=$_POST[$source]?>" />
        </div>
    <?php
    }
    ?>
    <input type="submit" value="buscar" />
</form>

<?php
if(!empty($_POST)) {
    var_dump($resultado);
}
?>

