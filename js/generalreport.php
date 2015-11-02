<?php 
error_reporting(0);
ob_start();
require('../libs/setup.php');
include_once('header_top.php');
$base_url = $fun->get_base_url();
$data = '';
$no_item_view = '';

$tbl_users						=	TABLE_PREFIX."users";
$tbl_products					=	TABLE_PREFIX."products";
$tbl_temp						=	TABLE_PREFIX."temp_transfer_stock";
$tbl_transfer_stock				=	TABLE_PREFIX."transfer_stock";
$tbl_product_inventory			=	TABLE_PREFIX."product_inventory";
$tbl_ts_history					=	TABLE_PREFIX."transfer_stock_history";
$tbl_product_stock_type			=	TABLE_PREFIX."product_stock_type";
$tbl_product_type				=	TABLE_PREFIX."product_type";
$tbl_product_sales_info			=	TABLE_PREFIX."product_sales_info";
$tbl_location					=   TABLE_PREFIX."location";
$tbl_calibres					=   TABLE_PREFIX."calibres";
$tbl_product_class				=   TABLE_PREFIX."product_class";
$tbl_manufacture				=   TABLE_PREFIX."manufacture";
$tbl_product_serial_egoss		=   TABLE_PREFIX."product_serial_egoss";
$tbl_transfer_stock				=   TABLE_PREFIX."transfer_stock";
$tbl_transfer_stock_history		=   TABLE_PREFIX."transfer_stock_history";
$tbl_sales_order				=   TABLE_PREFIX."sales_order";
$tbl_sales_order_details		=   TABLE_PREFIX."sales_order_details";
$tbl_customer					=	TABLE_PREFIX."customer";

$added_date=date('Y-m-d');

$redirect				=	'generalreport.php';


$currency = $company->get_default_currency();

//view 
$trans_id	='';
if(isset($_GET['viewTranId']) && !empty($_GET['viewTranId']))
{
	$trans_id	=	$_GET['viewTranId'];
}

if(isset($_GET['pid']) && !empty($_GET['pid']))
{
	$get_pid	=	$_GET['pid'];
}
//Company info

$company_data =  $company->get_company_details();

//echo '<pre>';print_r($company_detail);
if( $company_data['msg']['success'] == 1  )
{
	$logo = SERVER_FOLDER_PATH.'/images/company_logo/'.$company_data['msg']['company']['logo'];
}
if( $company_data['msg']['error'] == 1  )
{
	$logo = SERVER_FOLDER_PATH.'/images/logo.png';
}

$countries=$company->get_countries($company_data['msg']['company']['country_id']);
$states=$company->get_states_by_country($company_data['msg']['company']['country_id'],$company_data['msg']['company']['state_id']);
$cities=$company->get_cities_by_state($company_data['msg']['company']['state_id'],$company_data['msg']['company']['city_id']);

$country=$countries['msg']['countries'][0]['name'];
$state=$states['msg']['states'][0]['name'];
$city=$cities['msg']['cities'][0]['name'];
//end company
	   
	$errmsg='';
    $trans_id = '';
    $sql_date ='';
	if(isset($_POST['btnshow']) && !empty($_POST['btnshow']))
	{
	    unset($_SESSION['report_for']);
        unset($_SESSION['get_totals']);
        unset($_SESSION['sql_order']);
        unset($_SESSION['from_date']);
        unset($_SESSION['to_date']);
        unset($_SESSION['upc_barcode']);  
        
	   $report_for = "";
       $from_date ="";
       $to_date = "";
        if(isset($_POST['report_for']) && !empty($_POST['report_for']))
        {
            $report_for = $_POST['report_for'];
        }
        if(isset($_POST['from_date']) && !empty($_POST['from_date']))
        {
            $from_date = $_POST['from_date'];
        }
        if(isset($_POST['to_date']) && !empty($_POST['to_date']))
        {
            $to_date = $_POST['to_date'];
        }
        if($from_date != "" )
        {
            $sql_date = ' AND added_date >= "'.$from_date.'"';
        }
        if($to_date )
        {
            $sql_date = ' AND added_date <= "'.$to_date.'"';
        }
        if($from_date != '' && $to_date != '')
        {
            $sql_date = ' AND added_date BETWEEN "'.$from_date.'" AND "'.$to_date.'"';
        }
        
        if(isset($_POST['upc_barcode']) && !empty($_POST['upc_barcode']))
        {
            $upc_barcode = $_POST['upc_barcode'];
        }
        
		$sql_product = "";
        $sql_order = "";
		if($upc_barcode != "")
        {
            $report_for = "product";
           
          $sql_upc_report =  mysql_query("SELECT so.id FROM ".TABLE_PREFIX."sales_order AS so
                            INNER JOIN ".TABLE_PREFIX."sales_order_details sod ON so.id = sod.order_id 
                            INNER JOIN ".TABLE_PREFIX."products pro ON sod.product_id = pro.id
                            WHERE pro.upc_barcode = '".trim($upc_barcode)."' AND so.is_deleted = 0");
			$arr_pro_order_id=array();
			while($row_order_pro=mysql_fetch_object($sql_upc_report))
            {
			    $arr_pro_order_id[]=$row_order_pro->id;
			}
			$arr_pro_order_id=array_filter($arr_pro_order_id);
			
			if(count($arr_pro_order_id) > 0)
            {			
				$pro_order_ids=implode(',',$arr_pro_order_id);
                $sql_order_pro = "SELECT 
                                (SELECT COUNT(id) FROM irg_sales_order iso WHERE iso.id IN (".$pro_order_ids.") AND is_import = 1 
                                                        AND is_deleted = 0 ".$sql_date." ) AS total_io,
                                (SELECT COUNT(id) FROM irg_sales_order iso WHERE iso.id IN (".$pro_order_ids.") AND is_email = 1 
                                                        AND is_deleted = 0 ".$sql_date.") AS total_uso,
                                (SELECT COUNT(id) FROM irg_sales_order iso WHERE iso.id IN (".$pro_order_ids.") AND is_import = 0 
                                                    AND is_email = 0 AND is_deleted = 0 ".$sql_date.") AS total_so";
				//$sql_order_pro .="(".$pro_order_ids.")";
                $sql_pre = $sql_order_pro ; 
                $sql_product = "SELECT
                (SELECT SUM(product_quantity * product_price) FROM irg_sales_order so
                    INNER JOIN irg_sales_order_details sod ON so.id= sod.`order_id`
                    WHERE so.id IN (".$pro_order_ids.") AND so.`is_email` = 0 AND so.`is_import` =0 AND is_deleted = 0 ".$sql_date.") AS so_total,
                (SELECT SUM(product_quantity * product_price) FROM irg_sales_order so
                    INNER JOIN irg_sales_order_details sod ON so.id= sod.`order_id`
                    WHERE so.id IN(".$pro_order_ids.") AND so.`is_email` = 1 AND is_deleted = 0 ".$sql_date.") AS uso_total,
                (SELECT SUM(product_quantity * product_price) FROM irg_sales_order so
                    INNER JOIN irg_sales_order_details sod ON so.id= sod.`order_id`
                    WHERE so.id IN (".$pro_order_ids.") AND  so.`is_import` = 1 AND is_deleted = 0 ".$sql_date.") AS io_total";
                    
                $sql_order = "SELECT so.so_no,so.added_date,so.is_email,so.is_import, CONCAT(cust.fname,' ',cust.lname) AS cust_name,
                                sod.product_quantity,so.gst_payment, so.total_price, so.order_payment,so.`other_taxes`,so.`shipping_charge`
                                FROM ".TABLE_PREFIX."sales_order AS so
                                INNER JOIN ".TABLE_PREFIX."sales_order_details sod ON so.id = sod.order_id 
                                INNER JOIN ".TABLE_PREFIX."products pro ON sod.product_id = pro.id
                                INNER JOIN ".TABLE_PREFIX."customer cust ON so.`cust_id` = cust.id
                                WHERE pro.upc_barcode = '".trim($upc_barcode)."' AND so.is_deleted = 0";
			}
            else
            {
                $sql_order_pro .="(0)";
                $sql_pre = $sql_order_pro ;
            }

        }
		
        if($report_for == 'tax')
        {
            $sql_pre = " SELECT COUNT(id) as total_order, SUM(other_taxes) AS total_other_taxes  ,
                        SUM(shipping_charge) AS total_shipping,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_deleted = 0 AND gst_payment = 'paid' ".$sql_date.") as gst_paid,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_deleted = 0 AND gst_payment = 'unpaid' ".$sql_date.") as gst_unpaid
                        FROM irg_sales_order
                        WHERE is_deleted = 0 ";
                        
             $sql_order = "SELECT CONCAT(cust.`fname`,' ',cust.lname) AS cust_name, so.`added_date`,so.`other_taxes`,so.`shipping_charge`,
                                so.total_price,so.is_email, so.is_import, so.is_from_admin,so.gst_payment, so.order_payment
                            FROM irg_sales_order so
                            LEFT JOIN irg_customer cust ON so.`cust_id` = cust.`id`
                            WHERE so.is_deleted = 0 "
                                .str_replace("added_date","so.added_date",$sql_date)." ORDER BY so.`added_date` DESC";
                                    
        }
        else if($report_for == 'so')
        {
            
             $sql_pre = " SELECT COUNT(id) as total_order, SUM(other_taxes) AS total_other_taxes  ,
                         SUM(shipping_charge) AS total_shipping,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_email = 0 AND is_import = 0  AND is_deleted = 0 AND gst_payment = 'paid' ".$sql_date.") as gst_paid,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_email = 0 AND is_import = 0  AND is_deleted = 0 AND 
                                        gst_payment = 'unpaid' ".$sql_date.") as gst_unpaid
                        FROM irg_sales_order
                        WHERE is_email = 0 AND is_import = 0 AND is_deleted = 0 ";
                        
             $sql_order = "SELECT CONCAT(cust.`fname`,' ',cust.lname) AS cust_name, so.`added_date`,so.`other_taxes`,so.`shipping_charge`,
                                so.total_price, so.is_from_admin,so.gst_payment, so.order_payment
                            FROM irg_sales_order so
                            LEFT JOIN irg_customer cust ON so.`cust_id` = cust.`id`
                            WHERE so.is_email = 0 AND so.is_import = 0 AND so.is_deleted = 0 "
                                .str_replace("added_date","so.added_date",$sql_date)." ORDER BY so.`added_date` DESC";
        }
        else if($report_for == 'uso')
        {
            $sql_pre = " SELECT COUNT(id) as total_order, SUM(other_taxes) AS total_other_taxes  ,
                         SUM(shipping_charge) AS total_shipping,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_email = 1  AND is_deleted = 0 AND gst_payment = 'paid' ".$sql_date.") as gst_paid,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_email = 1  AND is_deleted = 0 AND gst_payment = 'unpaid' ".$sql_date.") as gst_unpaid
                        FROM irg_sales_order
                        WHERE is_email = 1 AND is_deleted = 0 ";
                        
             $sql_order = "SELECT CONCAT(cust.`fname`,' ',cust.lname) AS cust_name, so.`added_date`,so.`other_taxes`,so.`shipping_charge`,
                                so.total_price, so.is_from_admin,so.gst_payment, so.order_payment
                            FROM irg_sales_order so
                            LEFT JOIN irg_customer cust ON so.`cust_id` = cust.`id`
                            WHERE so.is_email = 1 AND so.is_import = 0 AND so.is_deleted = 0 ".
                            str_replace("added_date","so.added_date",$sql_date)." ORDER BY so.`added_date` DESC";
        }
        else if($report_for == 'io')
        {
            $sql_pre = " SELECT COUNT(id) as total_order, SUM(other_taxes) AS total_other_taxes  ,
                        SUM(import_fee) AS total_import_fee, SUM(import_shipping_charge) AS total_io_shipping,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_import = 1  AND is_deleted = 0 AND gst_payment = 'paid' ".$sql_date.") as gst_paid,
                        (SELECT SUM(gst_charge)
                                FROM irg_sales_order
                                WHERE is_import = 1  AND is_deleted = 0 AND gst_payment = 'unpaid' ".$sql_date.") as gst_unpaid
                        FROM irg_sales_order
                        WHERE is_import = 1 AND is_deleted = 0 ";
                        
             $sql_order = "SELECT CONCAT(cust.`fname`,' ',cust.lname) AS cust_name, so.`added_date`,so.`other_taxes`,so.`shipping_charge`,
                                so.total_price,so.import_fee, so.import_shipping_charge,so.gst_payment
                            FROM irg_sales_order so
                            LEFT JOIN irg_customer cust ON so.`cust_id` = cust.`id`
                            WHERE so.is_import = 1 AND so.is_deleted = 0 "
                            .str_replace("added_date","so.added_date",$sql_date)." ORDER BY so.`added_date` DESC";
        }
        
        if($report_for == 'product')
            $sql_pre = $sql_pre;
        else
            $sql_pre = $sql_pre.$sql_date; 
            
        $sql_order .= " LIMIT 0, ".record_per_page;
        $_SESSION['report_for'] = $report_for;
        $_SESSION['get_totals'] = $sql_pre;
        $_SESSION['sql_order'] = $sql_order;
        $_SESSION['from_date'] = $from_date;
        $_SESSION['to_date'] =$to_date;
        $_SESSION['upc_barcode'] = $upc_barcode; 
//die();
        
	}//end of button add
	
	if(isset($_POST['btnReset']) && !empty($_POST['btnReset']))
	{
	   
		unset($_POST);
        unset($_SESSION['report_for']);
        unset($_SESSION['get_totals']);
        unset($_SESSION['sql_order']);
        unset($_SESSION['from_date']);
        unset($_SESSION['to_date']);
        unset($_SESSION['upc_barcode']);  
		
	}//end of button reset

//start paging 

if(isset($_SESSION['sql_order']))
{
    $sql_order =$_SESSION['sql_order']; 
    $sql_order = str_replace(" LIMIT 0, ".record_per_page, "",$sql_order);
}
if(isset($_SESSION['report_for']) && !empty($_SESSION['report_for']))
{
    $report_for = $_SESSION['report_for'];
    
}
if(isset($_SESSION['get_totals']) && !empty($_SESSION['get_totals']))
{
    $sql_pre = $_SESSION['get_totals'];
    
}
if(isset($_SESSION['get_totals']) && !empty($_SESSION['get_totals']))
{
    $sql_pre = $_SESSION['get_totals'];
    
}
if(isset($_SESSION['from_date']) && !empty($_SESSION['from_date']))
{
    $from_date = $_SESSION['from_date'];
    
}
if(isset($_SESSION['to_date']) && !empty($_SESSION['to_date']))
{
    $to_date = $_SESSION['to_date'];
    
}
if(isset($_SESSION['upc_barcode']) && !empty($_SESSION['upc_barcode']))
{
    $upc_barcode = $_SESSION['upc_barcode'];
    
}

$url = $redirect;
$rowsPerPage = record_per_page;
$pageNum = 1;
if (isset($_GET['page'])) {
    $pageNum = $_GET['page'];
}

$offset = ($pageNum - 1) * $rowsPerPage;

$paging = $sql_order;
$sql_order = $sql_order . " LIMIT $offset, $rowsPerPage";
$self = $url;


$i = 0;

if ($_GET['page'] >= 1) {
    $i = $i + ($_GET['page'] - 1) * $rowsPerPage;
}


//end paging	
/******************************** Start creating PDF ************************************/
if(isset($_POST['btnCreatePDF']) && !empty($_POST['btnCreatePDF']))
{
$col_pdf='';
$html_pdf='';
$html_pdf_trans='';


$sql_pdf = "SELECT ".$col_pdf.", t.shipped_status,t.received_status,t.qty,(SELECT l.name FROM ".$tbl_location." l WHERE l.id=t.from_loc) as from_loc,(SELECT l2.name FROM ".$tbl_location." l2 WHERE l2.id=t.to_loc) as to_loc,CONCAT(c.fname,' ',c.lname) as cname,CASE WHEN ( p.product_type_id = 2 AND ( p.from_date <= now() AND p.to_date >= now() ) )  THEN psf.sale_price ELSE psf.customer_price END as cost FROM ".$tbl_products." p  LEFT JOIN ".$tbl_ts_history." t ON p.id=t.pid LEFT JOIN ".$tbl_product_serial_egoss." pse ON pse.id=t.serial_barcode_id LEFT JOIN ".TABLE_PREFIX."customer c on pse.cust_id = c.id LEFT JOIN ".$tbl_product_sales_info." psf on p.id = psf.product_id LEFT JOIN ".$tbl_transfer_stock." ts ON t.trans_stock_id=ts.id WHERE t.trans_stock_id='".$trans_id."' AND t.id>0";



//echo $sql_pdf;


$html_pdf='

<table style="width:1000px;">
    <tr>
     <td   style=" width:70px;">&nbsp;</td>
      <td style="padding-right:10px; text-align:center; font-size:10px;  width:600px; line-height:15px">
       <img  src="'.$logo.'"  width="150" alt=""/><br />
       '.$company_data['msg']['company']['address1'].'<br>
       '.$company_data['msg']['company']['address2'].'
       '.$city.', '.$state.', '.$country.'   Postal Code : '.$company_data['msg']['company']['postal_code'].' , Fax : '.$fun->format_phone( $company_data['msg']['company']['fax'] ) .', Contact No. : '.$fun->format_phone( $company_data['msg']['company']['phone'] ).'<br />
       Licence No : '.$company_data['msg']['company']['licence_no'].', BIN No. : '.$company_data['msg']['company']['bin_no'].',
       <a href="'.SiteUrl.'" >'.SiteUrl.'</a>
      </td>
      <td  style=" width:50px;">&nbsp;</td>
    </tr>
   </table>
   <br/>
   <br/>
   <table style="width:700px;">
    <tr> 
     <td style="  width:762px; border-top:1px solid #000; padding:6px 0; font-size:25px; border-bottom:1px solid #000; vertical-align:top; line-height:21px; text-align:center; "><b>Purchased & Transfer Invoice</b></td>
    </tr>
   </table>
   <br/>
   <br/>
<table style=" width:800px;  word-wrap: break-word;" cellspacing="0" cellpadding="0" border="1"><tr>
   <th style=" width:6px;  word-wrap: break-word; text-align:center;" >#</th>
  
   <th style=" word-wrap: break-word;padding:4px 3px;" >Description</th>
   <th style="word-wrap: break-word; padding:4px 3px;text-align:center;" > Quantity</th>
   <th style="word-wrap: break-word;padding:4px 3px;" >Purchased From </th>
   <th style="word-wrap: break-word;padding:4px 3px;" >Shipped To </th>
   <th style="word-wrap: break-word;padding:4px 3px;" >Price</th> 
   <th style="word-wrap: break-word;padding:4px 3px;" >Amount</th>
 
   
  </tr>';

$result_pdf=mysql_query($sql_pdf);
$p=1;$error_msg="";
$desc='';
$tot_amount=0;
$html_pdf_trans='';
while($row=mysql_fetch_object($result_pdf)){
    $desc = "";
//$html_pdf_trans='';

	if($row->frt_number!=''){
		$desc='FRT Number : '.$row->frt_number.'<br>';
	}
	if($row->upc_barcode!=''){
		$desc.='UPC/Barcode : '.$row->upc_barcode.'<br>';
	}
	if($row->dept!=''){
		$desc.='Dept. : '.$row->dept.'<br>';
	}
	if($row->category!=''){
		$desc.='category : '.$row->category.'<br>';
	}
	if($row->product_name!=''){
		$desc.='Product Name : '.$row->product_name.'<br>';
	}
	if($row->model!=''){
		$desc.='Model : '.$row->model.'<br>';
	}
	if($row->class!=''){
		$desc.='Class : '.$row->class.'<br>';
	}
	if($row->calibre!=''){
		$desc.='Caliber : '.$row->calibre.'<br>';
	}
	if($row->manufacturer!=''){
		$desc.='Manufacturer : '.$row->manufacturer.'<br>';
	}
	
	if($row->stock_type!=''){
		$desc.='Stock Type : '.$row->stock_type.'<br>';
	}

	if($row->product_type!=''){
		$desc.='Product Type : '.$row->product_type.'<br>';
	}
	if($row->featured!=''){
		$desc.='Featured : '.$row->featured.'<br>';
	}
	
	if($row->firearm!=''){
		$desc.='Arm Type : '.$row->firearm.'<br>';
	}
	if($row->length!=''){
		$desc.='Length : '.$row->length.'<br>';
	}
	if($row->height!=''){
		$desc.='Height : '.$row->height.'<br>';
	}
	if($row->height!=''){
		$desc.='Height : '.$row->height.'<br>';
	}
	if($row->barrel_length!='' && $row->barrel_length>0){
		$desc.='Barrel Length : '.$row->barrel_length.'<br>';
	}
	if($row->capacity!=''){
		$desc.='Capacity : '.$row->capacity.'<br>';
	}
	if($row->link!=''){
		$desc.='Link : '.$row->link.'<br>';
	}
	if($row->remark!=''){
		$desc.='Capacity : '.$row->remark.'<br>';
	}
	
	$tot_amount+=$row->cost;

if($row->shipped_status=='0' && $row->received_status=='1'){$status='Received';}else{$status='Shipped';}

$html_pdf_trans.='<tr>
<td valign="top"  style=" width:15px; padding:4px 3px;  text-align:center;">'.$p.'</td>
<td style=" width:220px; padding:4px 3px;" valign="top">'.$desc.'</td>
<td valign="top"  style="padding:4px 3px; width:50px; text-align:center;" >'.$row->qty.'</td>
<td valign="top" style="padding:4px 3px; width:130px;  word-wrap: break-word;" valign="top">'.$row->from_loc.'</td>
<td valign="top"  style="padding:4px 3px; width:110px; " >'.$row->to_loc.'</td>
<td valign="top" style="padding:4px 3px; width:50px; text-align:center;">$'.$fun->convert_price_two_deci($row->cost).' USD</td>
<td valign="top" style="padding:4px 3px; width:50px; text-align:center;">$'.$fun->convert_price_two_deci($row->cost).' USD</td>';
$html_pdf_trans.='</tr>';
$p++;
}//end of while
//$html_pdf_trans.='</table></td></tr>';
$html_pdf.=$html_pdf_trans;

$html_pdf.=
'<tr>
<td valign="top"  colspan="6" style=" width:15px;padding:4px 3px;  text-align:right;"><b>Total</b></td>
<td valign="top"  style=" width:15px;padding:4px 3px;  text-align:center;">$'.$fun->convert_price_two_deci($tot_amount).' USD </td>
</tr>
</table>';

$html	=	'<page style="font-size: 12px;font-family:"Open Sans","Helvetica Neue",Helvetica,Arial,sans-serif;line-height:15px;">'.$html_pdf.' </page>';
		

		  //$report_file	=	'transfered_stock_'.$trans_id.'.html';
                    
		  $doc_root =SERVER_FOLDER_PATH."admin/irg_print_labels_html/irg_general_report.html";
		  $file_to_save = $doc_root;
		
		//echo $html;
		//die;	
		header('Content-Type: text/html; charset=utf-8');
		$content = fopen($file_to_save, "w");
		$stuff	=	$html;
		fputs($content, $stuff);
		fclose($content);
		ob_start();
 	    include_once($file_to_save);
	//include_once('pdf.html');
        $content = ob_get_clean();
 		$output	= "irg_print_labels_html/irg_general_report.pdf";
		
    // convert to PDF
    require_once('html2pdf/html2pdf.class.php');
    try
    {

	    
        $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'utf-8', 3);
		$html2pdf->setDefaultFont('Arial'); 
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
       	$html2pdf->Output($output); // save pdf
		$_SESSION['msg']="<font color='#009900'>PDF created successfully.</font>";
		 
    }
    catch(HTML2PDF_exception $e) {
        echo "<script>alert('".$e."');</script>";
        
        exit;
    }



}//end of pdf button if	


/*********************************End PDF            ************************************/	
	
	
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<title> <?php echo SiteName.' | Reports'; ?> </title>
<!--=== CSS ===-->
<!-- Bootstrap -->
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

<!-- jQuery UI -->
<!--<link href="plugins/jquery-ui/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />-->
<!--[if lt IE 9]>
		<link rel="stylesheet" type="text/css" href="plugins/jquery-ui/jquery.ui.1.10.2.ie.css"/>
	<![endif]-->
<!-- Theme -->
<link href="assets/css/main.css" rel="stylesheet" type="text/css" />
<link href="assets/css/plugins.css" rel="stylesheet" type="text/css" />
<link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />
<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="assets/css/fontawesome/font-awesome.min.css">
<!--[if IE 7]>
		<link rel="stylesheet" href="assets/css/fontawesome/font-awesome-ie7.min.css">
	<![endif]-->
<!--[if IE 8]>
		<link href="assets/css/ie8.css" rel="stylesheet" type="text/css" />
	<![endif]-->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
<!--=== JavaScript ===-->
<script type="text/javascript" src="assets/js/libs/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/libs/lodash.compat.min.js"></script>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
		<script src="assets/js/libs/html5shiv.js"></script>
	<![endif]-->
<!-- Smartphone Touch Events -->
<script type="text/javascript" src="plugins/touchpunch/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="plugins/event.swipe/jquery.event.move.js"></script>
<script type="text/javascript" src="plugins/event.swipe/jquery.event.swipe.js"></script>
<!-- General -->
<script type="text/javascript" src="assets/js/libs/breakpoints.js"></script>
<script type="text/javascript" src="plugins/respond/respond.min.js"></script>
<!-- Polyfill for min/max-width CSS3 Media Queries (only for IE8) -->
<script type="text/javascript" src="plugins/cookie/jquery.cookie.min.js"></script>
<script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.horizontal.min.js"></script>
<!-- Page specific plugins -->
<!-- Charts -->
<!--[if lt IE 9]>
		<script type="text/javascript" src="plugins/flot/excanvas.min.js"></script>
	<![endif]-->
<script type="text/javascript" src="plugins/sparkline/jquery.sparkline.min.js"></script>
<script type="text/javascript" src="plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="plugins/flot/jquery.flot.tooltip.min.js"></script>
<script type="text/javascript" src="plugins/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="plugins/flot/jquery.flot.time.min.js"></script>
<script type="text/javascript" src="plugins/flot/jquery.flot.growraf.min.js"></script>
<script type="text/javascript" src="plugins/easy-pie-chart/jquery.easy-pie-chart.min.js"></script>
<script type="text/javascript" src="plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="plugins/blockui/jquery.blockUI.min.js"></script>
<script type="text/javascript" src="plugins/fullcalendar/fullcalendar.min.js"></script>
<!-- App -->
<!-- for header soring -->
<link rel="stylesheet" type="text/css" media="all" href="assets/header_sort/css/styles.css">
<script type="text/javascript" src="assets/header_sort/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(function(){
  $('#keywords').tablesorter(); 
});
</script>
<!-- End-->
<script type="text/javascript" src="assets/js/app.js"></script>
<script type="text/javascript" src="assets/js/plugins.js"></script>
<script type="text/javascript" src="assets/js/plugins.form-components.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		"use strict";

		App.init(); // Init layout and core plugins
		Plugins.init(); // Init all plugins
		FormComponents.init(); // Init all form-specific plugins
		
// print report ends
   
           	$( "#from_date, #to_date" ).datepicker({
					  showOtherMonths: true,
					  selectOtherMonths: true,
					  dateFormat: 'yy/mm/dd',
					   changeYear: true,
					  changeMonth: true,
					  yearRange: "-100:+50" 
				});
                
		$('#scan_barcode').change(function(e){
			var upc 	  = $(this).val();
			var total_row = $('#total_no_rows').val();
			for( var i=1;i<=total_row;i++)
			{
				if( $('#scan_barcode_'+i).val() == upc ) {
					var shipped_status = $('#scan_barcode_'+i).data('shipped_status');
					var received_status = $('#scan_barcode_'+i).data('received_status');
					var div_ids = $('#scan_barcode_'+i).data('row_id');
					var trans_stock_id = $('#scan_barcode_'+i).data('trans_stock_id');
					var product_id = $('#scan_barcode_'+i).data('product_id');
					var tid = $('#scan_barcode_'+i).data('tid');
						
					break;	
				}	
				else{
					var div_ids = 0; 
					var tid = 0; 
					var trans_stock_id = 0; 
					var shipped_status = 0; 
					var received_status = 0; 
				}
			}
			
			
			$.post('ajax/ajax_transfer_stock.php', {'action':'update_ts_status','tid':tid, 'product_id':product_id, 'trans_stock_id':trans_stock_id, 'shipped_status':shipped_status, 'received_status':received_status}, function(data){
				console.log(data);
				var result = JSON.parse(data);
				if( result.success == 1 )
				{
					$('#show_msg').html(result.msg);
					$('#show_msg').css('color','#009900');
					$('#scan_barcode_'+div_ids).data('shipped_status','0');
					$('#scan_barcode_'+div_ids).data('received_status','1');
					$('#status_'+div_ids).html('Received');
					
				}
				else
				{
					$('#show_msg').html(result.msg);
					$('#show_msg').css('color','#FF0000');
				}
			});
		});
	});
	
//empty function
function emptyVal()
{
			$( "#upc_barcode" ).val( '');
			$( "#serial_no" ).val( '' );
			$( "#customer_name" ).val( '' );
			$( "#non_firearm_barcode" ).val( '' );
			$( "#qty" ).val( '' ).attr('readonly','readonly');
								
			$( "#from_location" ).val( '' ).attr('disabled','disabled');
			$( "#to_location" ).val( '') ;
			$( "#product_id" ).val( '' );
			$( "#product_name" ).val( '' );
			
			$( "#model" ).val( '' );
			//$( "#department" ).val( ui.item.dept_id ).attr('disabled','disabled');
			$( "#manufacturer" ).val( '' );
			$( "#calibre" ).val( '' );
			$( "#qty" ).val( '' );
			$( "#barrel_length" ).val( '' );
			$( "#class" ).val( '' );
			$( "#frt_number" ).val( '' );
}
//
function checkEmptyValues()
{
    if(document.getElementById('upc_barcode').value != '')
    {
        if(document.getElementById('to_location').value==''){
    		alert("Please select to location");
    		document.getElementById('to_location').focus();
    		return false;
    	}
        
    }
    
    if(document.getElementById('report_for').value == '0')
    {
        if(document.getElementById('upc_barcode').value == '')
        {
        		alert("Please select to location");
        		document.getElementById('to_location').focus();
        		return false;
        }
        alert("Please select to location");
    	   document.getElementById('to_location').focus();
    	   return false;	
    }//end uso check
    
	
  
 
}//end of function	
//
function resetVal(){

 if($("#non_firearm_barcode").val()==''){
    $("#customer_name").val('').attr('readonly','readonly');
	//$("#qty").val('').removeAttr('readonly');
	//$("#availble_qty").val('');
	$("#from_location").removeAttr('disabled','disabled');
	$("#serial_barcode_id").val('');
 
 }

}
	
	</script>
<script type="text/javascript">
function barcode(default_val, ajax_page, div_id)
{ 
		
		$("#"+div_id).autocomplete({
			source: function(request, response) {
			
			$( "#qty" ).val( '' ).attr('readonly','readonly');
			$( "#from_location" ).val( '' ).attr('disabled','disabled');
			//$( "#category" ).val( '' ).removeAttr('disabled');
			
				if(($('#'+div_id).val()).length<4) {
					var object1 = request;
					var firearm=$("input[type='radio'][name='is_firearm']:checked").val();
					var p_id = $('#product_id').val();
					var object2 = { "product_id" : p_id, 'is_firearm':firearm };
					var json  = $.extend( object1, object2);
					$.getJSON('ajax/'+ajax_page, json,  response);
				}
				else
				{
					
					var object1 = request;
					var object2 = { "product_id" : '' };
					var json  = $.extend( object1, object2);
					$.getJSON('ajax/'+ajax_page, json,  function(data){  
					console.log((data));  
					var items = [];
					$.each( data, function( key, val ) {
										
					//func_get_category('ajax/get_po_category.php?cat_id='+val.dept_id);
					//if( val.length>0 ) {
					$( "#upc_barcode" ).val( val.upc_barcode );
					$( "#serial_no" ).val( val.serial_number );
					$( "#from_location" ).val( val.loc_id );
					//$( "#non_firearm_barcode" ).val( val.non_firearm_barcode );
					$( "#product_id" ).val( val.id );
					$( "#product_name" ).val( val.product_name ).attr('readonly','readonly');

					$( "#model" ).val( val.model ).attr('readonly','readonly');

					$( "#manufacturer" ).val( val.manu_id ).attr('disabled','disabled');
					$( "#calibre" ).val( val.calibre_id ).attr('disabled','disabled');
					$( "#barrel_length" ).val( val.barrel_length ).attr('disabled','disabled');
					$( "#class" ).val( val.class_id ).attr('disabled','disabled');
					$( "#frt_number" ).val( val.frt_number ).attr('readonly','readonly');
					
					$( "#availble_qty" ).val( val.qty ).attr('readonly','readonly');
													
                  
					$( "#customer_name" ).val( val.cname ).attr('readonly','readonly');
					
					$( "#from_loc" ).val( val.loc_id );
					

					//for firearm product
					if(val.firearm =='1' ) 
						{
							
							if($( "#serial_no" ).val() !='' || $( "#customer_name" ).val() !='' )
							{
							    $( "#serial_barcode_id" ).val(val.pse_id);
								$( "#qty" ).val( '1' ).attr('readonly','readonly');
								
								$( "#from_location" ).val( val.loc_id ).attr('disabled','disabled');
							}
							else{
							
								$( "#qty" ).val( '1' ).attr('readonly','readonly');
								
								$( "#from_location" ).val( val.loc_id ).attr('disabled','disabled');
							}	
							
						}
					//for non-firearm product

								
					if(val.firearm =='0' ) 
						{
							
						if($( "#non_firearm_barcode" ).val() =='')
							{
							  $( "#customer_name" ).val( '' ).attr('readonly','readonly');
							  $( "#qty" ).val(val.qty).removeAttr('readonly');
								$( "#from_location" ).val( val.loc_id ).removeAttr('disabled');
						}							
						 if($( "#non_firearm_barcode" ).val() !='' || $( "#customer_name" ).val() !='' )
							{
							    $( "#serial_barcode_id" ).val(val.pse_id);
								$( "#qty" ).val( val.qty ).attr('readonly','readonly');
								
								$( "#from_location" ).val( val.loc_id ).attr('disabled','disabled');
							}
							else{
									
								$( "#qty" ).val(val.qty).removeAttr('readonly');
								$( "#from_location" ).val( val.loc_id ).removeAttr('disabled');
							}	
							
						}	
						
															
						/*	get_sel_cats(val.dept_id, val.cat_id);
							$('#category_id').val(val.cat_id);
							$( "#category" ).attr('disabled','disabled');
								console.log($( "#category" ));
							}
							else
							{
								$( "#product_id" ).val( '' );
							}	*/
							items.push( val  );
						});
						 
					});
				}
			},
			minLength: 1,
			focus: function( event, ui ) {
				if(ajax_page == 'ts_autocompleter_barcode_no.php')
				 $( "#"+div_id ).val( ui.item.upc_barcode );
				if(ajax_page == 'ts_autocompleter_serial_no.php') 
				 $( "#"+div_id ).val( ui.item.serial_number );
				 if(ajax_page == 'ts_autocompleter_non_firearm_barcode.php') 
				 $( "#"+div_id ).val( ui.item.non_firearm_barcode );
				
				return false;
			},
			select: function( event, ui ) {
				//console.log(ui);
				//console.log(event);
				
					/*if( ui.length>0 ){*/
					$( "#upc_barcode" ).val( ui.item.upc_barcode );
					$( "#serial_no" ).val( ui.item.serial_number );
					//$( "#non_firearm_barcode" ).val( ui.item.non_firearm_barcode );
					
					$( "#product_id" ).val( ui.item.id );
					$( "#product_name" ).val( ui.item.product_name ).attr('readonly','readonly');

					$( "#model" ).val( ui.item.model ).attr('readonly','readonly');
					$( "#manufacturer" ).val( ui.item.manu_id ).attr('disabled','disabled');
					$( "#calibre" ).val( ui.item.calibre_id ).attr('disabled','disabled');
					$( "#barrel_length" ).val( ui.item.barrel_length ).attr('readonly','readonly');
					$( "#class" ).val( ui.item.class_id ).attr('disabled','disabled');
					$( "#frt_number" ).val( ui.item.frt_number ).attr('readonly','readonly');						
					$( "#customer_name" ).val( ui.item.cname ).attr('readonly','readonly');
					$( "#availble_qty" ).val( ui.item.qty ).attr('readonly','readonly');
					$( "#from_loc" ).val( ui.item.loc_id );	
					
					 console.log('availble_qty='+$( "#availble_qty" ).val());
					//for firearm product
					if(ui.item.firearm =='1' ) 
						{
							
							if($( "#serial_no" ).val() !='' || $( "#customer_name" ).val() !='' )
							{
							    $( "#serial_barcode_id" ).val(ui.item.pse_id);
								$( "#qty" ).val( '1' ).attr('readonly','readonly');
								
								$( "#from_location" ).val( ui.item.loc_id ).attr('disabled','disabled');
							}
							else{
							
								$( "#qty" ).val( '1' ).attr('readonly','readonly');
								
								$( "#from_location" ).val( ui.item.loc_id ).attr('disabled','disabled');
							}	
							
						}
					//for non-firearm product

								
					if(ui.item.firearm =='0' ) 
						{
							
						
						if($( "#non_firearm_barcode" ).val() =='')
							{
							  
							  $( "#customer_name" ).val( '' ).attr('readonly','readonly');
							  $( "#qty" ).val(ui.item.qty).removeAttr('readonly');
								$( "#from_location" ).val( ui.item.loc_id ).removeAttr('disabled');
						 }								  
						 if($( "#non_firearm_barcode" ).val() !='' || $( "#customer_name" ).val() !='' )
							{
							   $( "#serial_barcode_id" ).val(ui.item.pse_id);
								$( "#qty" ).val( ui.item.qty ).attr('readonly','readonly');
								
								$( "#from_location" ).val( ui.item.loc_id ).attr('disabled','disabled');
							}
							else{
									
								$( "#qty" ).val(ui.item.qty).removeAttr('readonly');
								$( "#from_location" ).val( ui.item.loc_id ).removeAttr('disabled');
							}	
							
						}	
							


						/*get_sel_cats(ui.item.dept_id, ui.item.cat_id);
						$('#category_id').val(ui.item.cat_id);
						$( "#category" ).attr('disabled','disabled');
						console.log($( "#category" ));*/
					/*}
					else		
					{
						$( "#product_id" ).val( '' );
					}*/
					return false;
			} 
		}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			  if( ajax_page == 'ts_autocompleter_serial_no.php' )
			  {    
				    return $( "<li>" )
					.append( "<a>" + item.serial_number + "</a>" )
					.appendTo( ul );
			  }
			  
			 
			if( ajax_page == 'ts_autocompleter_barcode_no.php' )
			 { return $( "<li>" )
				.append( "<a>" + item.upc_barcode + "</a>" )
				.appendTo( ul );
			}
			
			if( ajax_page == 'ts_autocompleter_non_firearm_barcode.php' )
			 { return $( "<li>" )
				.append( "<a>" + item.non_firearm_barcode + "</a>" )
				.appendTo( ul );
			}
		};		
		//};
}



<!-- function checkedAllAtOnce End-->  
function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;	
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e)	{		
	try{			
		xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
	}
	catch(e){
			try{
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e1){
				xmlhttp=false;
			}
		}
	}
	
	return xmlhttp;
 }//end of http function
function func_product_qty(strURL) {	


	var req = getXMLHTTP();
	
	if (req) {
	
	req.onreadystatechange = function() { 
		if (req.readyState == 4) {
		// only if "OK" 
			
			if (req.status == 200) {						
			  document.getElementById('div_qty').innerHTML=req.responseText;
			  document.getElementById('availble_qty').value=document.getElementById('qty').value;
			 						
			} else {
			//alert("There was a problem while using XMLHTTP:\n" + req.statusText);
			}
		}				
	}			
	req.open("GET", strURL, true);
	req.send(null);
	}
  }//end of function
  
  function print_report()// print report start
   {
    //open_lightbo(angry) $('#lightbox_main'), $('#lightbox_inner_box') );
    var report_for = $('#report').data('report_for');
    var date_from  = $('#report').data('date_from');
    var date_to    = $('#report').data('date_to');
    var upc        = $('#report').data('upc');
    
    
	$('#message_box').html('').css('display','none');
	$('#loading_image_box').css('display','block');
	open_lightbox($('#lightbox_main'), $('#lightbox_inner_loading_save') );
    $.post('ajax/ajax_print_report.php', { 'action':'print_report','report_for':report_for,'from_date': date_from,'to_date':date_to, 'upc':upc}, function(data){
             var results = JSON.parse(data);
             if( results.success==1 )
             {
        	   setTimeout(function(){ 
        			 $('#loading_image_box').css('display','none');
        			close_lightbox( $('#lightbox_main'), $('#lightbox_inner_loading_save') ); 
        			window.open(results.file);
        			},2000); 
             }
             else
             {
               $('#loading_image_box').css('display','none');
        	   close_lightbox( $('#lightbox_main'), $('#lightbox_inner_loading_save') ); 
               console.log(results.error_msg);
               alert( results.error_msg );
             }
    });
   }
   
</script>
<!-- Demo JS -->
<script type="text/javascript">
	$(document).ready(function() {
	//##### Send delete Ajax request to response.php #########
	$("body").on("click", "#responds .remove_btn", function(e) {
		
		 e.returnValue = false;
		 var clickedID = this.id.split('-'); //Split string (Split works as PHP explode)
		 var DbNumberID = clickedID[1]; //and get number from array
		

		var myData = 'recordToDelete='+ DbNumberID; //build a post data structure
		 
			jQuery.ajax({
			type: "POST", // HTTP method POST or GET
			url: "ajax/del_temp_stock.php", //Where to make Ajax calls
			dataType:"text", // Data type, HTML, json etc.
			data:myData, //Form variables
			success:function(response){
				//on success, hide  element user wants to delete.
				$('#item_'+DbNumberID).fadeOut("slow");
			},
			error:function (xhr, ajaxOptions, thrownError){
				//On error, we alert user
				alert(thrownError);
			}
			});
		});	
	 
	//##### Send delete Ajax request to response.php #########
		 
	 //add serial number
	 
	 
	    $("#btn_save_close").click(function(e) {
		  e.preventDefault();
		 
		
		
		   var  val = $("#frm_label_pdf").serialize();  
		   
		 	var myData=val;
			jQuery.ajax({
			type: "POST", // HTTP method POST or GET
			url: "ajax/ajax_transfer_stock.php", //Where to make Ajax calls
			//dataType:"text", // Data type, HTML, json etc.
			data:myData, //Form variables
			
			success:function(response){
			var results = JSON.parse(response);
				if(results.error=='1'){
			      alert(results.msg);
				}
				else{				
						
				 	var val2=results.msg.join();
					
					$("#txt_label_pdf").val(val2);
					close_lightbox($('#lightbox_main'), $('#lightbox_inner_box'));
					
				}
				
			 },
				error:function (xhr, ajaxOptions, thrownError){
				//On error, we alert user
				alert(thrownError);
				}
			});	
		});
	


   /*Add to btnCreatePDF button click*/
	$('#btnSelectLabelPdf').click(function(e){
	
		e.preventDefault();
		open_lightbox($('#lightbox_main'), $('#lightbox_inner_box'));
		
						
	}) //end of serail no
	//button cancel
		$('#lightbox_main, #btn_cancel').click(function(e){
		e.preventDefault();
		close_lightbox($('#lightbox_main'), $('#lightbox_inner_box'))
		});

});

function open_lightbox(lightbox_main, lightbox_inner)
{
	var winW = $(window).width();
    var winH = $(window).height();
	var objW = $('.child-1').outerWidth();
    var objH = $('.child-1').outerHeight();
    var left = (winW / 2) - (objW / 2);
    var top = (winH / 2) - (objH / 2);

    lightbox_main.css('height', winH + "px");
    lightbox_main.fadeTo('slow', 0.6);
    lightbox_inner.css({ 'left': left + "px", 'top': top });
    lightbox_inner.fadeTo('slow',0.8);
}
function close_lightbox(lightbox_main, lightbox_inner)
{
	lightbox_inner.fadeOut('slow');
	lightbox_main.fadeOut('slow');
}
</script>
<!-- lightbox popup-->
<style type="text/css">
#lightbox_main {
	position: fixed;
	z-index: 9000;
	background-color: #000;
	display: none;
	top: 0px;
	left: 0px;
	width: 100%;
}
.lightbox_inner {
	left: 29% !important;
	top: 14% !important;
	opacity: 1 !important;
	display: none;
	position: fixed;
	z-index: 9999;
	border-radius: 4px;
	-webkit-border-radius: 4px;
	-moz-border-radius:  4px;
	border-radius: 4px;
	-webkit-box-shadow: 0 0px 25px rgba(0, 0, 0, .5);
	-moz-box-shadow: 0 0px 25px rgba(0, 0, 0, .5);
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5);
	border: 5px solid rgb(000, 0, 0);
	border: 5px solid rgba(000, 0, 0, .5);/*width: 490px;*/
}
#popup-wrapper {
	width:600px;
	overflow:scroll;
	height:450px;
	padding:20px;
	background-color: #fff;
	color:#999999;
	border-radius: 4px;
	-webkit-border-radius: 4px;
	-moz-border-radius:  4px;
	border-radius: 4px;
	border:3px solid #000000;
	font-size:20px;
}
#popup-wrapper1 {
	padding:20px;
	background-color: #fff;
	color:#999999;
	border-radius: 4px;
	-webkit-border-radius: 4px;
	-moz-border-radius:  4px;
	border-radius: 4px;
	border:3px solid #000000;
	font-size:20px;
	float:left;
	height: 374px;
}
div#account_login {
	padding: 1px 19px;
	width: 86%;
}
.accontright a {
	float: right;
	color: #1e51ba;
	font-size: 13px;
	font-family: 'RobotoLight';
	padding-right: 6px;
	padding-top: 19px;
}
.bottitle {
	margin-top: 29px;
	margin-bottom: 0px;
	background: url("images/ac_titlebg.png") no-repeat scroll 0 0 transparent;
	height: 40px;
	width: 100%;
	color: #fff;
	text-align: center;
	font-size: 18px;
	position: absolute;
	bottom: 30px;
	text-transform: uppercase;
	line-height: 42px;
	margin-left: 3px;
}
</style>
</head>
<body <?php if($trans_id!=''){?>onload=document.getElementById('scan_barcode').focus();<?php }?>>
<!-- Header -->
<?php require('header.php'); ?>
<!-- /.header -->
<style type="text/css">
#lightbox_main {
position: fixed;
z-index: 9000;
background-color: #000;
display: none;
top: 0px;
left: 0px;
width: 100%;
}
.lightbox_inner, .lightbox_inner1
{
display: none;
position: fixed;
z-index: 999999;
-webkit-border-radius:6px;
-moz-border-radius:6px;
border-radius: 6px;
-webkit-box-shadow: 0 0px 25px rgba(0,0,0,.5);
-moz-box-shadow: 0 0px 25px rgba(0,0,0,.5);
box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5);
border: 5px solid rgb(000, 0, 0);
border: 5px solid rgba(000, 0, 0, .5);
/*width: 490px;*/
}

#popup-wrapper {
width:740px;
height: 450px;
background-color: #fff;
overflow: scroll;
}
.lightbox_inner1 .text-popup1 {
background: none repeat scroll 0 0 #fff;
border: 2px solid #ddd;
color: #842121;
font-size: 20px;
/* height: 65px !important; */
left: 39% !important;
opacity: 1 !important;
padding: 16px 0;
position: fixed;
text-align: center;
top: 39% !important;
width: 400px !important;
text-align: left;
padding: 10px;
}
.lightbox_inner label {
}
.lightbox_inner input[type="text"], .lightbox_inner select {
background: none repeat scroll 0 0 #FFFFFF;
border: 1px solid #CCCCCC;
border-radius: 4px;
box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
color: #555555;
line-height: 1.42857;
padding: 6px 12px;

}

</style>
<div id="lightbox_main"></div>

<!-- Loading popup -->

<div id="lightbox_inner_loading_save" class="lightbox_inner1 text-popup01">
  <div id="popup-wrapper1" class="child-1 text-popup">
    <div id="loading_image_box"> <img src="../images/save_loading_img.gif"> </div>
    <div id="message_box"></div>
  </div>
</div>
<!-- End-->
<div id="container">
  <?php require('sidebar.php'); ?>
  <!-- /Sidebar -->
  <div id="content">
    <div class="container inventorypage">
		<!-- Breadcrumbs line -->
		<div class="crumbs">
			<ul id="breadcrumbs" class="breadcrumb">
				<li> <i class="icon-home"></i> <a href="index.php">Dashboard</a> </li><li><a href="generalreports.php"><span>Report</span></a></li>
				<li><span>Report</span></li>
			</ul>
			<ul class="crumb-buttons">
				<li class="range"><i class="icon-calendar"></i> <span></span></li>
			</ul>
		</div>
		<!-- /Breadcrumbs line -->
		<!--=== Page Content ===-->
		<!-- /Statboxes -->
		<!--=== Blue Chart ===-->
		<!--Add popup -->
<div id="lightbox_main"></div>
<div id="lightbox_inner_box" class="lightbox_inner inventorypage">
	<a href="#" id="btn_cancel" class="btn_close">close</a>
	<div id="popup-wrapper" class="child-1">
		<div class="widget box">
			<div class="widget-header">
				<h4><i class="icon-reorder"></i>Choose PDF Label</h4>
			</div>
			<div class="widget-content">
				<form method="post" id="frm_label_pdf">
					<input type="hidden" name="hdn_del_serailno_ids" id="hdn_del_serailno_ids">
					<input type="hidden" name="action" id="action" value="pdf_label">
					<table class="potbl" cellpadding="2" cellspacing="0" border="0" width="100%">
						<?php 
						$result=mysql_query("SELECT upc_barcode,product_name,frt_number,is_firearm,is_featured,is_duty_charged,dept_id,
						cat_id,calibre_id ,manu_id,class_id,stock_type_id,product_type_id,model,description,
						barrel_length,capacity,measurement_length,measurement_height,measurement_width,
						measurement_weight,link,remark FROM ".TABLE_PREFIX."products ");
						
						// Print the column names as the headers of a table
						$r=1;  
						for($i = 0; $i < mysql_num_fields($result); $i++) 
						{
							$field_info = mysql_fetch_field($result, $i);
							// echo "<th>{$field_info->name}</th>";
							if($r=="1") {echo '<tr>';}
							if($field_info->name=='dept_id'){$name='dept';$col_val="(SELECT name FROM ".TABLE_PREFIX."category d WHERE p.dept_id=d.categoryID) as dept";}
							elseif($field_info->name=='cat_id'){$name='category';$col_val="(SELECT name FROM ".TABLE_PREFIX."category c WHERE p.cat_id=c.categoryID) as category";}
							elseif($field_info->name=='is_duty_charged'){$name='duty charged';$col_val="CASE WHEN p.is_duty_charged=1 THEN 'Yes'  ELSE 'No' END as duty_charged";}
							elseif($field_info->name=='calibre_id'){$name='caliber';$col_val="(SELECT name FROM ".TABLE_PREFIX."calibres cal WHERE p.calibre_id=cal.id) as calibre";}
							elseif($field_info->name=='manu_id'){$name='manufacturer';$col_val="(SELECT name FROM ".TABLE_PREFIX."manufacture m WHERE p.manu_id=m.id) as manufacturer";}
							elseif($field_info->name=='class_id'){$name='class';$col_val="(SELECT class_name FROM ".TABLE_PREFIX."product_class c1 WHERE p.class_id=c1.id) as class";}
							elseif($field_info->name=='stock_type_id'){$name='stock type';$col_val="(SELECT type_name FROM ".TABLE_PREFIX."product_stock_type pst WHERE p.stock_type_id=pst.id) as stock_type";}
							elseif($field_info->name=='product_type_id'){$name='product type';$col_val="(SELECT type_name FROM ".TABLE_PREFIX."product_type pt WHERE p.product_type_id=pt.id) as product_type";}
							elseif($field_info->name=='is_featured'){$name='featured';$col_val="CASE WHEN p.is_featured=1 THEN 'Yes'  ELSE 'No' END as featured";}
							elseif($field_info->name=='is_firearm'){$name='firearm';$col_val="CASE WHEN p.is_firearm=1 THEN 'Firearm'  ELSE 'Non-Firearm' END as firearm";}  
							elseif($field_info->name=='measurement_length'){$name='length';$col_val="measurement_length as length";}
							elseif($field_info->name=='measurement_height'){$name='height';$col_val="measurement_height as height";}
							elseif($field_info->name=='measurement_width'){$name='width';$col_val="measurement_width as width";}
							elseif($field_info->name=='measurement_weight'){$name='weight';$col_val="measurement_weight as weight";}
							elseif($field_info->name=='barrel_length'){$name='barrel length';$col_val=$field_info->name;}
							elseif($field_info->name=='product_name'){$name='product name';$col_val=$field_info->name;}
							elseif($field_info->name=='upc_barcode'){$name='UPC/Barcode';$col_val=$field_info->name;}
							elseif($field_info->name=='frt_number'){$name='frt number';$col_val=$field_info->name;}
							else{$name=$field_info->name;$col_val=$field_info->name;}
						?>
							<td valign="top" class="text_left">
								<input type="checkbox" name="chk_label_pdf[]" class="form-control" value= "<?php echo $col_val ?>"/>
								<?php echo ucwords($name) ?>
							</td>
							<?php if($r=="4")
							{
								echo ' </tr>';
									$r=0;
							} $r++;
						} ?>
						<tr>
							<td ></td>
						</tr>
					</table>
					<div class="row">
						<div class="col-md-12"></div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-3">
								<input type="button" class="btn btn-primary" name="btn_save_close" id="btn_save_close" value="Save & Close" title="Save & Close" >
							</div>
						</div>
					</div>
						<table cellpadding="3" cellspacing="2" border="0" width="100%">
							<tr>
								<td width="17%"></td>
							</tr>
							<tr>
								<td  valign="top" width="22%">&nbsp;</td>
								<td  valign="top" >	&nbsp;	<td>
								<td  valign="top" width="15%">&nbsp;</td>
							</tr>
						</table>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- ENd-->    
	<?php 
		if(isset($errmsg) && !empty($errmsg) || isset($_SESSION['msg']) && !empty($_SESSION['msg'])){?>
		<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" class="no_record">
			<tr>
			<td colspan="7" align="center">
				<div  class="errormsg">
                    <?php $errmsg = $_SESSION['msg'];  ?>
					<?php   echo "<strong>".$errmsg.'</strong>';unset($errmsg);unset($_SESSION['msg']);  ?> 
                   
				</div>
			</td>
			</tr>
		</table>
    <?php } ?>
      <div class="row">
        <div class="col-md-12">
         
            <div class="widget box">
              <div class="widget-header">
                <h4><i class="icon-reorder"></i>Report's</h4>
              </div>
			  
              <div class="widget-content">
			  <!--Search box start here -->
				
					 
				<div class="alert alert-warning">
					 <form id="frm_transfer_stock" name="frm_transfer_stock"  method="post" action="generalreport.php">
					 
					 <input type="hidden" name="product_id" id="product_id" />
					 <input type="hidden" name="from_loc" id="from_loc" />
					 <input type="hidden" name="serial_barcode_id" id="serial_barcode_id" />
				
					<div class="row">
						<div class="col-md-12"> 
							<div class="col-md-3">
                                <label class="control-label">Report For:</label>
                                
                                <select name="report_for" class="form-control">
                                    <option value="0">Select</option>
                                    <!--option value="tax" 
                                        <?php //if(isset($report_for)){if($report_for == "tax") echo 'selected= "selected"' ; } ?>
                                        >All</option-->
                                    <option value="so"
                                    <?php if(isset($report_for)){if($report_for == "so") echo 'selected= "selected"' ; } ?>
                                        >Total in SO</option>
                                    <option value="uso"
                                    <?php if(isset($report_for)){if($report_for == "uso") echo 'selected= "selected"' ; } ?>
                                        >Total in USO</option>
                                    <option value="io"
                                    <?php if(isset($report_for)){if($report_for == "io") echo 'selected= "selected"' ; } ?>
                                        >Total in IO</option>
                                </select>
                            </div>						
							<div class="col-md-3">
								<label class="control-label">UPC/Barcode:</label>
								<input type="text" name="upc_barcode" id="upc_barcode" class="form-control" value="<?php  echo  $_POST['upc_barcode']; ?>" placeholder="UPC or Barcode" onKeyUp="barcode('1','ts_autocompleter_barcode_no.php','upc_barcode');"/>
							</div>
							<div class="col-md-3">
								<label class="control-label">From Date:</span></label>
								<input type="text" name="from_date" id="from_date" class="form-control" 
                                <?php if(isset($from_date)){ echo 'value = "'.$from_date.'"' ; } ?>/>
							</div>
                            <div class="col-md-3">
								<label class="control-label">From To:</span></label>
								<input type="text" name="to_date" id="to_date" class="form-control"
                                 <?php if(isset($to_date)){ echo 'value = "'.$to_date.'"' ; } ?> 
                                    />
							</div>
						</div>
                    </div>
					
                    <div class="row">
						<div class="col-md-12"> 
							<div class="col-md-4">
								<input name="btnshow" type="submit" value="Show" title="show" class="btn btn-primary vmar" onClick="return checkEmptyValues()">
								<input name="btnReset" type="submit" value="Reset" title="Reset" class="btn btn-primary vmar" >
							</div>
						</div>
					</div>
					</form><!-- End of form stock-->
                </div>
				<!--Search box end here -->
			   
				<!-- Message Box start here -->
			   
				<!-- Message Box end here -->
		<form id="frm_manage_transfer_prducts" name="frm_manage_transfer_prducts"  method="post">
		<input type="hidden" name="txt_label_pdf" id="txt_label_pdf" />
				
                
                <table width="100%" border="0" cellspacing="3" cellpadding="3" >
					<tr>
						<td >
							<div id="show_msg" ></div>
						</td>
					</tr>
				</table>
			
				<div class="dataTables_scrollBody deepscroll ">
                <div class="col-md-3">
                <label><?php // if( ($report_for == 'so' || $report_for == 'uso' || $report_for == 'tax') && $num_rows>0 ) echo $report_for; ?></label>
                </div>
					<?php 
                    $get_totals=mysql_query($sql_pre);
    	           $num_rows=mysql_num_rows($get_totals);
                    if($num_rows>0 && $report_for == 'io')
                    { ?> 
						<div id="responds"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>Total Orders</span></th>
									<th ><span>Total Paid GST(CAD)</span></th>
									<th ><span>Total Unpaid GST(CAD)</span></th>
									<th ><span>Total Other Taxes(CAD)</span></th>
									<th ><span>Total Import Fee(USD)</span></th>
                                    <th ><span>Total Import Shipping(USD)</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  <?php while($row = mysql_fetch_object($get_totals)) {?>
                                <tr>
							        <td><?php echo $row->total_order ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->gst_paid) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->gst_unpaid) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->total_other_taxes) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->total_import_fee) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->total_io_shipping) ; ?></td>
									<!--td><?php //echo '$'.$fun->convert_price_two_deci($cost).' USD';?></td-->
								</tr>
								<?php  } ?>
                                <tr>
                                        <td colspan="12" align="center" class="paglist">
                                            <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                        </td>
                                    </tr>
								</tbody>
								</table>
						</div>
                        
                        <div id="responds">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>#</span></th>
									<th ><span>Customer Name</span></th>
                                    <th ><span>Order Date</span></th>
                                    <th ><span>Import Fee</span></th>
                                    <th ><span>Import Shipping</span></th>
									<th ><span>Taxes(CAD)</span></th>
                                    <td><span>GST Payment</span></td>
									<th ><span>Shipping (CAD)</span></th>
                                    <th ><span>Order Total (USD)</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  
                                  <?php
                                  $count = 1; 
                                  $order = mysql_query($sql_order);
                                  while($row = mysql_fetch_object($order)) {?>
                                    <tr>
    							        <td><?php echo $count++; ?></td>
                                        <td><?php echo $row->cust_name ; ?></td>
                                        <td><?php echo $row->added_date; ?></td>
                                        <td><?php echo $row->import_fee; ?></td>
                                        <td><?php echo $row->import_shipping_charge; ?></td>
                                        <td><?php echo $row->other_taxes; ?></td>
                                        <td><?php echo $row->gst_payment; ?></td>
                                        <td><?php echo $row->shipping_charge; ?></td>
    									<td><?php echo $row->total_price;?></td>
    								</tr>
    								<?php  } ?>
                                    <tr>
                                        <td colspan="12" align="center" class="paglist">
                                            <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                        </td>
                                    </tr>
								</tbody>
								</table>
						</div>
                        
				  <div class="row" style="padding-top:8px;">
					<div class="col-md-9"  >
						<a style="color:#FFFFFF" href="javascript:void(0)" target="_blank" title="View &amp; Download PDF" 
                        class="btn btn-primary" onclick="print_report();" 
                        data-report_for="<?php echo $report_for ; ?>" 
                        data-date_from="<?php echo $from_date; ?>" 
                        data-date_to="<?php echo $to_date; ?>"
                        data-upc="<?php echo $upc_barcode; ?>" id="report"
                        >View &amp; Download PDF</a>
					</div>
         
                  </div>
                  <?php } 
                  else if( ($report_for == 'so' || $report_for == 'uso' || $report_for == 'tax') && $num_rows>0 )
                    {
                        
                        ?>
                        <div id="responds"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>Total Orders</span></th>
									<th ><span>Total Paid GST(CAD)</span></th>
									<th ><span>Total Unpaid GST(CAD)</span></th>
									<th ><span>Total Other Taxes(CAD)</span></th>
                                    <th ><span>Total Shipping(CAD)</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  <?php while($row = mysql_fetch_object($get_totals)) {?>
                                <tr>
							        <td><?php echo $row->total_order ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->gst_paid) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->gst_unpaid) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->total_other_taxes) ; ?></td>
                                    <td><?php echo $fun->convert_price_two_deci($row->total_shipping) ; ?></td>
									<!--td><?php //echo '$'.$fun->convert_price_two_deci($cost).' USD';?></td-->
								</tr>
								<?php  } ?>
                                 <tr>
                                    <td colspan="12" align="center" class="paglist">
                                        <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                    </td>
                                </tr>
								</tbody>
								</table>
						</div>
                        
                        <div id="responds">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>#</span></th>
									<th ><span>Customer Name</span></th>
                                    <th ><span>Order Date</span></th>
									<th ><span>Taxes(CAD)</span></th>
                                    <th><span>GST Payment</span></th>
									<th ><span>Shipping (CAD)</span></th>
                                    <th ><span>Order Total (USD)</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  
                                  <?php
                                  $count = 1; 
                                  $order = mysql_query($sql_order);
                                  while($row = mysql_fetch_object($order)) {?>
                                <tr>
							        <td><?php echo $count++; ?></td>
                                    <td><?php echo $row->cust_name ; ?></td>
                                    <td><?php echo $row->added_date; ?></td>
                                    <td><?php echo $row->other_taxes; ?></td>
                                    <td><?php echo $row->gst_payment; ?></td>
                                    <td><?php echo $row->shipping_charge; ?></td>
									<td><?php echo $row->total_price;?></td>
								</tr>
								<?php  } ?>
                                <tr>
                                        <td colspan="12" align="center" class="paglist">
                                            <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                        </td>
                                    </tr>
								</tbody>
								</table>
						</div>
                        
                        <div class="row" style="padding-top:8px;">
					<div class="col-md-9"  >
						<a style="color:#FFFFFF" href="javascript:void(0)" target="_blank" title="View &amp; Download PDF"
                         class="btn btn-primary" onclick="print_report();" 
                        data-report_for="<?php echo $report_for ; ?>" 
                       data-date_from="<?php echo $from_date; ?>" 
                        data-date_to="<?php echo $to_date; ?>"
                        data-upc="<?php echo $upc_barcode; ?>" id="report"
                        >View &amp; Download PDF</a>
						<input class="btn btn-primary" type="button" name="btnCancel" id="btnCancel" value="Cancel"  title="Cancel" onClick="window.location.href='transfer_stock.php';" />
					</div>
         
                  </div>
                        <?php
                    } 
                    else if($report_for == 'product' && $num_rows>0 )
                    {
                        
                        ?>
                        <div id="responds"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>Total Quantity Sold</span></th>
									<th ><span>Total Sold in SO</span></th>
									<th ><span>Total Sold in IO</span></th>
									<th ><span>Total Sold in USO</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  <?php 
                                  while($row = mysql_fetch_object($get_totals)) {?>
                                <tr>
							        <td><?php echo $row->total_so + $row->total_io + $row->total_uso; ?></td>
                                    <td><?php echo $row->total_so ; ?></td>
                                    <td><?php echo $row->total_io ; ?></td>
                                    <td><?php echo $row->total_uso; ?></td>
									<!--td><?php //echo '$'.$fun->convert_price_two_deci($cost).' USD';?></td-->
								</tr>
								<?php  } ?>
								</tbody>
								</table>
						</div>
                        <div class="col-md-12">
                            <h3>Revenue Generated</h3>
                        </div>
                        <div id="responds"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>Total Sales</span></th>
									<th ><span>Total Sale SO</span></th>
									<th ><span>Total Sale IO</span></th>
									<th ><span>Total Sale USO</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  <?php
                                  $get_values = mysql_query($sql_product);
                                  while($row = mysql_fetch_object($get_values)) {?>
                                <tr>
							        <td><?php echo '$'.$fun->convert_price_two_deci($row->so_total + $row->io_total + $row->uso_total); ?></td>
                                    <td><?php echo '$'.$fun->convert_price_two_deci($row->so_total); ?></td>
                                    <td><?php echo '$'.$fun->convert_price_two_deci($row->io_total ); ?></td>
                                    <td><?php echo '$'.$fun->convert_price_two_deci($row->uso_total); ?></td>
									<!--td><?php //echo '$'.$fun->convert_price_two_deci($cost).' USD';?></td-->
								</tr>
								<?php  } ?>
                                 <tr>
                                        <td colspan="12" align="center" class="paglist">
                                            <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                        </td>
                                    </tr>
								</tbody>
								</table>
						</div>
                        <div id="responds"><table width="100%" cellspacing="0" cellpadding="0" border="0" class="pub_table adminlist" id="keywords" >
							 <thead>
								<tr>
									<th ><span>#</span></th>
                                    <th><span>SO #</span></th>
									<th ><span>Customer Name</span></th>
                                    <th ><span>Order Date</span></th>
									<th ><span>Order Quantity</span></th>
                                    <th><span>GST Payment</span></th>
                                    <th><span>Taxes</span></th>
									<th ><span>Shipping (CAD)</span></th>
                                    <th ><span>Order Total (USD)</span></th>
								</tr>
								 </thead>
								  <tbody>
                                  
                                  <?php
                                  $count = 1; 
                                  $order = mysql_query($sql_order);
                                  
                                  while($row = mysql_fetch_object($order)) {?>
                                <tr>
							        <td><?php echo $count++; ?></td>
                                    <td><?php echo $row->so_no; ?></td>
                                    <td><?php echo $row->cust_name ; ?></td>
                                    <td><?php echo $row->added_date; ?></td>
                                    <td><?php echo $row->product_quantity; ?></td>
                                    <td><?php echo $row->gst_payment; ?></td>
                                    <td><?php echo $row->other_taxes; ?></td>
                                    <td><?php echo $row->shipping_charge; ?></td>
									<td><?php echo $row->total_price;?></td>
								</tr>
								<?php  } ?>
                                <tr>
                                        <td colspan="12" align="center" class="paglist">
                                            <div class="pagination" align="center"><?php echo $fun->paging_admin($paging, $rowsPerPage, $pageNum, $self); ?> </div>
                                        </td>
                                    </tr>
								</tbody>
								</table>
						</div>
                        <div class="row" style="padding-top:8px;">
					<div class="col-md-9"  >
						<a style="color:#FFFFFF" href="javascript:void(0)" target="_blank" title="View &amp; Download PDF"
                         class="btn btn-primary" onclick="print_report()"
                            data-report_for="<?php echo $report_for ; ?>" 
                            data-date_from="<?php echo $from_date; ?>" 
                            data-date_to="<?php echo $to_date; ?>"
                            data-upc="<?php echo $upc_barcode; ?>" id="report"
                         >View &amp; Download PDF</a>
						
					</div>
         
                  </div>
                        <?php
                    }
                    else if(isset($_POST) && !empty($_POST))
                    {
				    	$msg='No search result found';
					} 
					echo '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="no_class">
							<tbody><tr><td align="center"><strong>'.$msg.'</strong></td><tr></tbody></table>';
					 ?>
                </div>
				
				</form>
              </div>
            </div>
         
        </div>
      </div>
	  <?php 
	  if(isset($_POST['btnTransferx']) && !empty($_POST['btnTransferx']))
		{
		 $barcode=$db->get_ts_barcode();//get barcode from db class
	    $ts_qty=mysql_query("INSERT INTO ".$tbl_transfer_stock." SET barcode='".$barcode."',added_date='".$added_date."'");
		 $trans_stock_id=mysql_insert_id();
		//echo '<br>';	
	  
	 // echo '<pre>';print_r($transferred_arr );die;
	  
	  if(isset($transferred_arr) && !empty($transferred_arr))
	 
	   
	   
	    foreach($transferred_arr as $t_row)
		{
			
			$pid= $t_row['pid'];
			$serial_egoss_id= $t_row['serial_egoss_id'];
			$from_loc_id= $t_row['from_loc_id'];
			$to_loc_id= $t_row['to_loc_id'];
			$qty= $t_row['qty'];
			$firearm= $t_row['is_firearm'];
						
			$whr="WHERE product_id='".$pid."' AND location_id='".$from_loc_id."'";
									
			//if qty already available for transfer location
			
				$whr2="WHERE product_id='".$pid."' AND location_id='".$to_loc_id."'";
			
			
				$row_bqty=mysql_fetch_object(mysql_query("SELECT quantity as qty FROM ".$tbl_product_inventory." $whr2 "));
				$avail_qty2=$row_bqty->qty;
				
				if($avail_qty2>0){
					$new_qty=$avail_qty2+$qty;
				}else{
					$new_qty=$qty;
				}
			
			//get used serial number	
			// $get_used_srno=mysql_num_rows(mysql_query("SELECT id FROM ".$tbl_product_serial_egoss." WHERE id='$serial_egoss_id' AND is_used=1"));
				
			//********************************** FIREARM *****************************************
			if($firearm=='1'){
				
				if($serial_egoss_id>0)
				{
				
						$trns_qry_pse=mysql_query("UPDATE ".$tbl_product_serial_egoss." SET received_loc_id='$to_loc_id' WHERE id='$serial_egoss_id'");
							
						$row_aqty=mysql_fetch_object(mysql_query("SELECT quantity as qty FROM ".$tbl_product_inventory." $whr "));
				
						$avail_qty=$row_aqty->qty;
					   if($avail_qty>0){
							$remain_qty=$avail_qty-$qty;
						}
				
					if($avail_qty2>0){
					
						$trns_qry2=mysql_query("UPDATE ".$tbl_product_inventory." SET quantity='$new_qty',location_id='$to_loc_id' $whr2");
				
					}
					else
					{
						$trns_qry3=mysql_query("INSERT INTO ".$tbl_product_inventory." SET product_id='".$pid."',quantity='".$new_qty."',location_id='$to_loc_id'");
					}
							
						$trns_qry4=mysql_query("UPDATE ".$tbl_product_inventory." SET quantity='$remain_qty' $whr");
						
					}
			 } //end of if
			 //******************************************* END ****************************************************
			
			//************************** for NON FIREARM	***************************************************************
			if($firearm=='0'){
			
			    $whr3="WHERE product_id='".$pid."' AND received_loc_id='".$from_loc_id."'";
									
			//if qty already available for transfer location
			
				$whr4="WHERE product_id='".$pid."' AND received_loc_id='".$to_loc_id."'";
				if($serial_egoss_id>0)
				{
				 
				
					$trns_qry_pse=mysql_query("UPDATE ".$tbl_product_serial_egoss." SET received_loc_id='$to_loc_id' WHERE id='$serial_egoss_id'");
					
				}
				else
				{
					
					
					$remain_cqty=0;
					
					$sql_cqty=mysql_query("SELECT id,cust_qty as qty FROM ".$tbl_product_serial_egoss." $whr3 ORDER BY cust_qty DESC");
					
					 $qflag=0;
					 $remain_cqty=0;
					 $c_qty=0;
					 $as_qty=0;
					while($row_cqty=mysql_fetch_object($sql_cqty)){
															
						 $c_qty+=$row_cqty->qty;
						
						
						if($qty>=$c_qty){
						     
							$qry_u2="UPDATE ".$tbl_product_serial_egoss." SET received_loc_id='$to_loc_id' WHERE id='".$row_cqty->id."'";
							mysql_query($qry_u2);
							
							$as_qty+=$c_qty;
						}elseif($as_qty<$qty){
						     $re_qty=$c_qty-$qty;
														
							 $qry_u="UPDATE ".$tbl_product_serial_egoss." SET cust_qty='".$re_qty."' WHERE id='".$row_cqty->id."'";
							 mysql_query($qry_u);
							
							 $new_erow=mysql_fetch_object(mysql_query( "SELECT inv_id,cost,product_id,cust_id,vendor_id FROM ".$tbl_product_serial_egoss." WHERE id='".$row_cqty->id."'"));
																					
							 mysql_query("INSERT INTO ".$tbl_product_serial_egoss." SET inv_id='".$new_erow->inv_id."',product_id='".$new_erow->product_id."',vendor_id='".$new_erow->vendor_id."',cost='".$new_erow->cost."',cust_qty='".$re_qty."',received_loc_id='$to_loc_id',added_date='".$added_date."'");
							
						
							
						  }
						
					}
					
					$row_aqty=mysql_fetch_object(mysql_query("SELECT quantity as qty FROM ".$tbl_product_inventory." $whr "));
				
					$avail_qty=$row_aqty->qty;
					if($avail_qty>0){
						$remain_qty=$avail_qty-$qty;
					}
				
					if($avail_qty2>0)
					{
					 
						$trns_qry2=mysql_query("UPDATE ".$tbl_product_inventory." SET quantity='$new_qty',location_id='$to_loc_id' $whr2");
				
					}
					else
					{
					
						$trns_qry3=mysql_query("INSERT INTO ".$tbl_product_inventory." SET product_id='".$pid."',quantity='".$new_qty."',location_id='$to_loc_id'");
				    }
				
				$trns_qry4=mysql_query("UPDATE ".$tbl_product_inventory." SET quantity='$remain_qty' $whr");
						
				}
			 } 
			 //************************************* end of if ********************************************************
			 	
				mysql_query("INSERT INTO ".$tbl_transfer_stock_history." SET trans_stock_id='".$trans_stock_id."',qty='".$qty."',pid='".$pid."',serial_barcode_id='".$serial_egoss_id."',from_loc='".$from_loc_id."',to_loc='".$to_loc_id."',shipped_status='1',added_date='".$added_date."'");
			
		}//end of loop
			//die;
			$_SESSION['msg']='<font color="green">Product transferred successfully.</font>';
			
			unset($transferred_arr);
			//empty table after transferred stock
			//mysql_query("TRUNCATE TABLE ".$tbl_temp."");
			header('location:transfer_stock.php');
  }
  
	  ?>
      <!-- /.row -->
      <!-- /Blue Chart -->
      <!-- /Page Content -->
    </div>
    <!-- /.container -->
  </div>
  <?php 
  
  
  include('footer.php');?>
</div>
</body>
</html>
