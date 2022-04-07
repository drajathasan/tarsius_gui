<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-04-07 08:49:27
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

use TarsiusGui\Plugin;
use TarsiusGui\Models\{Tarsius,Module};
use Zein\Storage\Local\Directory;

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require __DIR__ . '/../helper.php';
// end dependency

// Autoload
tarsiusAutoload();

// privileges checking
$can_read = utility::havePrivilege('system', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

$page_title = 'Tarsius GUI';

/* Action Area */
parse_str(file_get_contents('php://input'), $_POST);

// Create plugin
if (isset($_POST['plugin_name']))
{
    // Retrieve data
    $data = Tarsius::find($_POST['id']??0);

    if (is_null($data))
    {
        $data = new Tarsius;
        $data->id = 0;
    }

    $attributeMap = [
        'plugin_name', 'plugin_uri',
        'description', 'version',
        'author', 'author_uri',
    ];
    
    if ($_POST['type'] !== 'hook')
    {
        $attribute = [
            'module_target' => $_POST['module_target'],
            'label' => $_POST['label']
        ];
    }
    else
    {
        $attribute = [
            'hook_target' => $_POST['hook_target']
        ];
    }

    foreach ($attributeMap as $attr) {
        $attribute[$attr] = $_POST[$attr]??'?';
    }

    $data->name = $_POST['plugin_name'];
    $data->type = $_POST['type'];
    $data->attribute = json_encode($attribute);
    $data->save();

    $Plugin = new Plugin;

    $Plugin->create($Plugin->option('plugin_name'), $Plugin);
}

// Delete Plugin
if (isset($_POST['itemAction']))
{
    $directory = new Directory;
    $directory->plugins = SB . 'plugins/';

    foreach ($_POST['itemID'] as $id) {
        // Retrieve data
        $data = Tarsius::find($id??0);

        if (!is_null($data))    
        {
            $packageDir = strtolower(str_replace(' ', '_', $data->name));
            $directory->deletePlugins($packageDir);
            $data->delete();
        }
    }

    utility::jsAlert('Plugin berhasil dihapus');

    $url = $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'list']);
    echo <<<HTML
        <script>
            parent.$('#mainContent').simbioAJAX('{$url}');
        </script>
    HTML;
    exit;
}

/* End Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'list']) ?>" class="btn btn-default" title="Plugin yang dibuat oleh Tarsius GUI">Daftar Plugin</a>
                <a href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'add']) ?>" class="btn btn-success" title="Membuat basis plugin SLiMS 9">Buat Plugin</a>
            </div>
            <form name="search" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'list']) ?>" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
                <input type="text" name="keywords" class="form-control col-md-3"/>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>"
                        class="s-btn btn btn-default"/>
            </form>
        </div>
    </div>
</div>

<?php
if ((isset($_GET['action']) && $_GET['action'] == 'add') || isset($_POST['itemID']))
{
    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell"';
    $form->table_content_attr = 'class="alterCell2"';
    
    // Retrieve data
    $data = Tarsius::find($_POST['itemID']??0);

    /* Form Element(s) */
    $dataAttribute = new stdClass;
    if (!is_null($data))
    {
        $form->edit_mode = true;
        $form->addHidden('id', $data->id);
        $dataAttribute = json_decode($data->attribute);
    }

    $form->addTextField('text', 'plugin_name', 'Nama Plugin', $data->name??'', 'class="form-control"');
    $form->addSelectList('type', 'Tipe Plugin', [['0','Pilih'],['datalist','Data List'],['report','Report'],['print','Print'],['hook','Hook']], $data->type??'', 'class="plugin select2"', 'Tipe Plugin');
    $form->addTextField('text', 'plugin_uri', 'Alamat Unduh Plugin', $dataAttribute->plugin_uri??'', 'class="form-control"');
    $form->addTextField('textarea', 'description', 'Deskripsi', $dataAttribute->description??'', 'class="form-control"');
    $form->addTextField('text', 'version', 'Versi Plugin', $dataAttribute->version??'1.0.0', 'class="form-control w-25"');
    $form->addTextField('text', 'author', 'Nama Pembuat Plugin', $dataAttribute->author??'', 'class="form-control"');
    $form->addTextField('text', 'author_uri', 'Alamat Medsos Pembuat', $dataAttribute->author_uri??'', 'class="form-control"');
    // load module
    $list = '<option value="0">Pilih</option>';

    $visibility = [
        'typeModule' => 'typeNonModule',
        'typeHook' => 'typeHook d-none',
        'module_target' => $dataAttribute->module_target??'',
        'label' => $dataAttribute->label??'',
        'hook_target' => '',
    ];

    foreach (Module::all() as $module) {
        $list .= '<option value="' . $module->module_name . '" ' . ($visibility['module_target'] == $module->module_name) . '>' . ucwords(str_replace('_', ' ', $module->module_name)) . '</option>';
    }

    if (is_object($data) && $data->type == 'hook')
    {
        $visibility = [
            'typeModule' => 'typeNonModule d-none',
            'typeHook' => 'typeHook',
            'hook_target' => $dataAttribute->hook_target??'',
            'label' => ''
        ];
    }

    extract($visibility);

    $form->addAnything('Kelengkapan Yang Lain', <<<HTML
        <div class="{$typeModule}">
            <label>Plugin akan muncul di Modul?</label>
            <select name="module_target" class="form-control w-25">{$list}</select>
            <label>Plugin akan muncul dengan label?</label>
            <input type="text" name="label" value="{$label}" class="form-control w-25">
        </div>
        <div class="{$typeHook}">
            <label>Plugin akan berjalan pada hook?</label>
            <input type="text" name="hook_target" value="{$hook_target}" class="form-control w-25">
        </div>
    HTML);

    // print out the form object
    echo $form->printOut();

    $url = $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'list']);
    echo <<<HTML
    <script>
        $('.plugin').change(function(){
            if ($(this).val() == 'hook')
            {
                $('.typeHook').removeClass('d-none');
                $('.typeNonModule').addClass('d-none');
            }
            else
            {
                $('.typeHook').addClass('d-none');
                $('.typeNonModule').removeClass('d-none');
            }
        })

        $('#mainForm').submit(async function(e){
            e.preventDefault();
            
            try {
                let request = await fetch('{$url}', {
                    method: 'POST',
                    body: $(this).serialize()
                });
                let response = await request.json();

                if (response.status)
                {
                    top.toastr.success('Sukses', response.message);
                    $('#mainContent').simbioAJAX('{$url}');
                    return true;
                }
                
                throw response.message;

            } catch (error) {
                top.toastr.error('Galat', error);
            }
        });
    </script>
    HTML;
}
else
{
    $table_spec = 'tarsius';
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('id', 'name "Nama Projek"', 'type Tipe', 'created_at "Tanggal Buat"', 'updated_at "Tanggal Diperbaharui"');

    if (isset($_GET['keywords']) AND $_GET['keywords']) 
    {
        $keywords = utility::filterData('keywords', 'get', true, true, true);
        $criteria = ' name like "%'.$keywords.'%"';
        // jika ada keywords maka akan disiapkan criteria nya
        $datagrid->setSQLCriteria($criteria);
    }

    $datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
    $datagrid->table_name = 'memberList';
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'] . '?' . httpQuery();

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, true); // object database, spesifikasi table, jumlah data yang muncul, boolean penentuan apakah data tersebut dapat di edit atau tidak.
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
        echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
    }
    // menampilkan datagrid
    echo $datagrid_result;
    /* End datagrid */
}
?>
<script>
    // $('.btn-danger').removeAttr('onclick');
    $('.btn-danger').click(function(e){
        alert('Menghapus data plugin juga menghapus direktori tersebut');
    })
</script>