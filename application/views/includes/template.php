<?php
$this->load->view('includes/header');
$this->load->view($view);
$this->load->view('includes/footer');
?>

<script>
var base_url	= '<?php echo base_url(); ?>';
var datetime	= '<?php echo $this->Commond_Model->getCurrentDateTime(); ?>';
</script>


