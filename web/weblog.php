<?php 
    include 'inc/header.php'; 
    include_once 'inc/funciones.php';

    $funciones = new Funciones();
?>

<div class="row">
    <div class="col-lg-12 p-4 ps-0">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline"><h2><i class="bi bi-arrow-left"></i></h2></a>
            <h2><i class="bi bi-file-earmark-binary"></i> Baúl de Logs</h2>
        </div>
        <hr />
    </div>
</div>

<div class="list-files">
    <!-- grid 4 vertical -->
    <div class="row">
        <?php
            $files = $funciones->GetFiles();
            foreach($files as $file){
                echo "
                    <div class='col-xs-6 col-md-5 col-lg-3 p-2'>
                        <div class='card shadow' style='cursor: pointer;' onclick='openModal(\"".$file['name']."\", \"".$file['content']."\", \"".$file['size']."\");'>
                            <div class='card-body'>
                                <div class='text-center'>
                                    <i class='bi bi-file-earmark-text display-2'></i>
                                </div>
                                <hr />
                                <div class='text-center'>
                                    <h5 class='card-title mb-0'>".$file['name']."</h5>
                                    <small class='text-muted'>".$file['size']."</small>
                                </div>
                            </div>
                        </div>
                    </div>
                ";
            }

            if(count($files) == 0){
                echo "
                    <div class='col-lg-12'>
                        <div class='alert alert-warning' role='alert'>
                            No se encontraron archivos.
                        </div>
                    </div>
                ";
            }
        ?>
    </div>
</div>

<!-- Modal show file -->
<div class="modal fade bd-view-modal-lg" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="ModalTitle">Log Viwer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class='modal-body'>
            <div class="data">
            </div>
        </div>

        <div class="modal-footer text-left">
        </div>
    </div>
  </div>
</div>

<script>
    function openModal(file, content, size){
        $('.bd-view-modal-lg').modal('show');
        $('.modal-title').html("LogViwer: <b>"+file+"</b>");

        //modal header add size and date of file aligned vertical
        $('.modal-footer').html("<small class='text-muted'>Size: "+size+" - </small><br /><small class='text-muted'>Last Modification: "+new Date().toLocaleString()+"</small>");

        //decode base64 UTF-8
        var data = atob(content);
        //reverse lines order
        //data = data.split("\n").reverse().join("\n");
        
        $('.data').html("<pre class='pre'>"+data+"</pre>");
        
        //enable scroll control in modal
        $('.pre').css('overflow-y', 'auto');
        $('.pre').css('max-height', $(window).height() * 0.72);

        //padding bottom 30px
        $('.pre').css('padding-bottom', '30px');

        //data autoscroll to bottom
        setTimeout(function(){
            $('.pre').scrollTop($('.pre')[0].scrollHeight);
        }, 300);

        //modal size
        $('.modal-dialog').css('max-width', $(window).width() * 0.95);
        $('.modal-dialog').css('max-height', $(window).height() * 0.8);
    }

</script>

<?php include 'inc/footer.php'; ?>