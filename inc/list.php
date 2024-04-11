<?php

use MongoDB\Collection;

$page = GETPOST('num_page') ? GETPOST('num_page') : 1;
$pagination = 25;
$params = '';
$arr_search = array();

function getSearch($field, $arr_search) {
    if (GETPOST('search_'.$field)) {
        $searchValue = GETPOST('search_'.$field);
        if (is_numeric($searchValue)) {
            $arr_search[$field] = (int)$searchValue;
        } else {
            $arr_search[$field] = new MongoDB\BSON\Regex($searchValue, 'i');
        }
    }
    return $arr_search;
}

$arr_search = [];
$filters = ['title', 'year', 'production', 'actors', 'synopsis', 'id_tmdb'];
for ($i = 0; $i < count($filters); $i++) {
    $arr_search = getSearch($filters[$i], $arr_search);
}

$mdb = new myDbClass();

$client = $mdb->getClient();
// $collections = $mdb->getCollections(); 
// foreach ($collections as $item) {
//     print $item['name'].'<br />';
//  };

$movies_collection = $mdb->getCollection('movies');
// print '<pre>';
// print_r($movies_collection);
// print '</pre>';
// $movies_collection = new Collection();
// print '<pre>';
// print_r($arr_search);
// print '</pre>';
$nb_elts = $movies_collection->countDocuments($arr_search);
$nb_pages = ceil($nb_elts / $pagination);
$cursor = $movies_collection->find(
    $arr_search,
    [
        'sort' => ['year' => -1],
        'skip' => ($page > 0 ? (($page - 1) * $pagination) : 0),
        'limit' => $pagination,
    ]
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
    'id_tmdb' => array('lbl' => 'TMDB', 'type' => 'text'),
);

?>
<div class="dtitle w3-third w3-left">Liste des elements (<?php echo $nb_elts; ?>)</div>
<div class="daddnew w3-third w3-center"><a href="./index.php?action=add">Ajouter un element</a></div>
<div class="dmorehtmlright w3-third w3-right">
    <?php
    if ($nb_pages <= 5) {
        for ($i_page = 1; $i_page <= $nb_pages; $i_page++) {
            print '<a href="index.php?action=list' . $params . '&num_page=' . ($i_page) . '">' . ($i_page) . '</a>&nbsp;';
        }
    } else {

        for ($i_page = 1; $i_page <= 5; $i_page++) {
            print '<a href="index.php?action=list' . $params . '&num_page=' . ($i_page) . '">' . ($i_page) . '</a>&nbsp;';
        }
        print '...&nbsp;';

        print '<a href="index.php?action=list' . $params . '&num_page=' . ($page - 1) . '">' . ' < ' . '</a>&nbsp;';
        print $page . '&nbsp;';
        print '<a href="index.php?action=list' . $params . '&num_page=' . ($page + 1) . '">' . ' > ' . '</a>&nbsp;';
        print '...&nbsp;';

        for ($i_page = $nb_pages - 5; $i_page <= $nb_pages; $i_page++) {
            print '<a href="index.php?action=list' . $params . '&num_page=' . ($i_page) . '">' . ($i_page) . '</a>&nbsp;';
        }
    }
    ?>
</div>
<div class="dcontent w3-container">
    <table class="w3-table w3-striped">
        <thead>
            <tr>
                <?php
                foreach ($cols as $key => $dtls) {
                ?>
                    <th><?php echo $dtls['lbl']; ?></th>
                <?php
                }
                ?>
            </tr>
            <form name="searchForm" class="w3-container" action="index.php?action=list" method="GET">
                <tr>
                    <?php
                    foreach ($cols as $key => $dtls) {
                    ?>
                        <th>
                            <?php
                            switch ($key) {
                                case '_id':
                            ?>
                                    <a href="javascript: submitSearchForm();">
                                        <i class="fas fa-search w3-hover-opacity" aria-hidden="true"></i>
                                    </a>
                                <?php
                                    break;
                                default:
                                ?>
                                    <input type="text" name="search_<?php echo $key; ?>" value="<?php echo GETPOST('search_' . $key); ?>" />
                            <?php
                                    break;
                            }
                            ?>
                        </th>
                    <?php
                    }
                    ?>
                    <th>
                    </th>
                </tr>
            </form>
        </thead>
        <tbody>
            <?php
            // foreach ($cursor as $document) {
            //     print print_tr_movie($document);
            // }
            while ($iterator->valid()) {
                $document = $iterator->current();

                // print '<pre>';
                // print_r($document);
                // print '</pre>';
                // print '<br />';

                print_tr_movie($document, $cols);

                $iterator->next();
            }

            ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    function submitSearchForm() {
        document.searchForm.submit();
    }
</script>