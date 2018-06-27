<div class="tab-pane active" id="result_dataset">
    <table class="table table-bordered" id ="list">
        <tr>
            <th colspan="11"><?= Yii::t('app', 'Your Authored Datasets') ?></th>
        </tr>
        <tr>
            <th class="span2"><?= Yii::t('app', 'DOI') ?></th>
            <th class="span6"><?= Yii::t('app', 'Title') ?></th>
            <th class="span2"><?= Yii::t('app', 'Dataset Type') ?></th>
            <th class="span2"><?= Yii::t('app', 'Status') ?></th>
            <th class="span2"><?= Yii::t('app', 'Publication Date') ?></th>
            <th class="span2"><?= Yii::t('app', 'Modification Date') ?></th>
            <th class="span2"><?= Yii::t('app','File Count') ?></th>
        </tr>

        <?php $data = $authoredDatasets; ?>
        <?php
        for ($i = 0; $i < count($authoredDatasets); $i++) {
            $class = $i % 2 == 0 ? 'even' : 'odd';
            if(isset($selected) && $data[$i]->id==$selected) {
                $class = 'submit-selected';
            }
            ?>

            <tr class="<?php echo $class; ?>" id="js-dataset-row-<?=$data[$i]->id?>">
                <?
                $upload_status = $data[$i]->upload_status;

                if ( $upload_status != 'Published' && $upload_status!='Private' ) { ?>
                    <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>">
                       unknown

                    </td>
                <? } else { ?>
                    <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>">
                        <? echo MyHtml::link("10.5524/" . $data[$i]->identifier, "/dataset/" . $data[$i]->identifier, array('target' => '_blank')); ?>
                    </td>
                <? } ?>
                <td class="left content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>"><? echo $data[$i]->title; ?> </td>
                <td >
                    <? foreach ($data[$i]->datasetTypes as $type) { ?>
                        <?= $type->name ?>

                    <? } ?>
                </td>
                <td><?= MyHtml::encode($data[$i]->upload_status) ?></td>
                <td><? echo MyHtml::encode($data[$i]->publication_date); ?> </td>
                <td><? echo MyHtml::encode($data[$i]->modification_date); ?> </td>
                <td><? echo count($data[$i]->files); ?></td>
                </tr>
            <? } ?>
    </table>
</div>
<script>
    $(".hint").tooltip({'placement': 'left'});

    $(".js-delete-dataset").click(function(e) {
        if (!confirm('Are you sure you want to delete this item?'))
            return false;
        e.preventDefault();
        var  did = $(this).attr('did');

        $.ajax({
           type: 'POST',
           url: '/dataset/datasetAjaxDelete',
           data:{'dataset_id': did},
           success: function(response){
            if(response.success) {
                $('#js-dataset-row-'+did).remove();
            } else {
                alert(response.message);
            }
          },
          error:function(){
        }
        });
    });

    $('a.delete').live('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?'))
            return false;
      e.preventDefault();
      $.ajax({
           type: 'POST',
           url: $(this).attr('href'),
           success: function(){
                window.location.reload();

          },
          error:function(){
            alert("Failure!")
//          $("#result").html('there is error while submit');
      }

        });


    });

</script>
