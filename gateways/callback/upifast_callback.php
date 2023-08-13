<?php 
/**
 * UPIFAST PAYMENT MODULE FOR WHMCS
 * @author AHK WEB SOLUTIONS
 * @Website https://ahkwebsolutions.com/
 * @license GPL V.1 
 * @Disclaimer Please do not temper the code 
 * settings.
 *
 * @return array
 */
$filename=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
$filename = str_replace("/modules/gateways/callback/upifast_callback.php",'/clientarea.php?action=invoices',$filename);
// header("Location: $filename");

if(isset($_GET['results']) && $_GET['results']!=NULL){
    ?>
    <html>
    <head>
    <title>User Cancelled Payment</title>
    </head>
    <body>
    	<center><h1>Please do not refresh this page...</h1></center>
    	<center><h3>Transaction  Cancelled by you! </h3></center>
    		<form method="GET" action="<?php echo $filename ?>" name="f1">
    		<script type="text/javascript">
    		 setTimeout(() => {
                        document.f1.submit();
                    }, 1500);
    		</script>
    	</form>
    </body>
    </html>
    <?php
    
}else if(isset($_POST['status']) && $_POST['status']!=NULL){
    ?>
        <html>
    <head>
    <title>We Are Processing your Order</title>
    </head>
    <body>
    	<center><h1 style="color:red;">Your Transaction is Being Process Please Wait...</h1></center>
    	<center><h5 style="color:green;">After Complete the Process you will be redirected to ClientArea...</h5></center>
    		<form method="post" action="upifast.php" name="f1">
    		<table border="1">
    			<tbody>
    			<?php
    			foreach($_POST as $name => $value) {
    				echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
    			}
    			?>
    			</tbody>
    		</table>
    		<script type="text/javascript">
    			document.f1.submit();
    		</script>
    	</form>
    </body>
    </html>
        <?php
}
?>

