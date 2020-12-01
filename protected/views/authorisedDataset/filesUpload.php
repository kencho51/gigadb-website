<?php ?>
<div class="content">
    <div id="gigadb-fuw">
        <article class="container">
            <header class="page-title-section">
                <div class="page-title">
                    <ol class="breadcrumb pull-right">
                        <li><a href="/">Home</a></li>
                        <li class="active">File Upload Wizard</li>
                    </ol>
                    <dataset-info identifier="<?= $identifier ?>" />
                </div>
            </header>
            <div>
                <section class="span6">
                    <uploader identifier="<?= $identifier ?>" endpoint="<?php echo $tusd_path ?>" />
                </section>
                <aside class="span4">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">Step 1: Uploading the files</h3>
                        </div>
                      <div class="panel-body">
                        <ul>
                        <li>This is the first step for submitting files associated to the dataset.</li>
                        <li>You can upload files from your computer by dragging them to the area on the left.</li>
                        <li>Once uploads are marked as "Complete", a "Next" button will appear at the bottom. You can click it to proceed to the next stage (annotating the files).</li>
                        </ul>
                      </div>
                    </div>
                </aside>
            </div>
            <footer>
                <pager identifier="<?= $identifier ?>" <?php echo "uploads-exist=\"$uploadsCount\"" ?>/>
            </footer>
        </article>
    </div>
</div>
