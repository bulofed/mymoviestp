<?php

$mdb = new myDbClass();

$client = $mdb->getClient();

$id = GETPOST('id');

if ($id != '') {
    $obj_id = new MongoDB\BSON\ObjectId($id);
    $movies_collection = $mdb->getCollection('movies');
    $cursor = $movies_collection->find(
        ['_id' => $obj_id],
        ['limit' => 1],
    );

    $cursor->setTypeMap(array('root' => 'array', 'document' => 'array', 'array' => 'array'));
    $iterator = new IteratorIterator($cursor);

    $iterator->rewind();
    $cols = array(
        '_id' => array('lbl' => '#', 'type' => 'id'),
        'title' => array('lbl' => 'Titre', 'type' => 'text'),
        'year' => array('lbl' => 'Année', 'type' => 'text'),
        'production' => array('lbl' => 'Production', 'type' => 'array'),
        'actors' => array('lbl' => 'Acteurs', 'type' => 'array'),
        'synopsis' => array('lbl' => 'Synopsis', 'type' => 'textarea'),
    );

    if ($iterator->valid()) {
        $document = $iterator->current();
        $elt = secure_document($document, $cols);
    }

    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        $movies_collection->deleteOne(['_id' => $obj_id]);
        header('Location: index.php');
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmer la suppression</title>
</head>
<body>
    <h2>Confirmer la suppression</h2>
    <form method="post">
        <p>Êtes-vous sûr de vouloir supprimer le film <strong><?php echo $elt['title']; ?></strong> ?</p>
        <input type="submit" name="confirm" value="yes">
        <input type="submit" name="confirm" value="no">
    </form>
</body>
</html>

<?php
} else {
    header('Location: index.php');
    exit();
}
?>