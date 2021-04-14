<?php
$this->pageTitle = 'GigaDB - File Types';

?>
<h1>GigaDB - File Types</h1>

<?php  $types = FileType::model()->findAll(); ?>
<table align="center" border="1" style="text-align: left;">
    <tr>
        <th style="text-align: center">Name</th>
        <th style="text-align: center">Description</th>
    </tr>
    <?php foreach ($types as $type): ?>
        <?php if (!empty($type->description)) { ?>
            <tr>
            <td><?php echo $type->name; ?></td>
            <td><?php echo $type->description; ?></td>
            </tr>
        <?php } ?>
    <?php endforeach; ?>
</table>


