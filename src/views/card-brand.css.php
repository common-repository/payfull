<?php
$visa_img_path = plugins_url( 'images/payfull_creditcard_visa.png', __FILE__ );
$master_img_path = plugins_url( 'images/payfull_creditcard_master.png', __FILE__ );
$not_supported_img_path = plugins_url( 'images/payfull_creditcard_not_supported.png', __FILE__ );
?>
<style type="text/css">
                      .input-cc-number-visa{background:rgba(0,0,0,0) url("<?php echo $visa_img_path; ?>") no-repeat scroll 97% center / 10% auto!important;float:left}
.input-cc-number-master{background:rgba(0,0,0,0) url("<?php echo $master_img_path; ?>") no-repeat scroll 97% center / 7% auto!important;float:left}
.input-cc-number-not-supported{background:rgba(0,0,0,0) url("<?php echo $not_supported_img_path; ?>") no-repeat scroll 97% center / 4% auto!important;float:left}
.joker{border-radius:25px;font-weight:600;padding:3px 10px;background:#ff9800;color:#fff;text-transform:uppercase}
#pf_window{max-width:600px!important;padding: 0px}
#payfullImage{padding:0;margin:0 auto;max-width:90px;box-shadow:none;display:block}
#bkmLogo{padding:0;margin:0 auto;box-shadow:none}
#pf_titleTab{height:61px!important;list-style-type:none;margin:0!important;padding:0!important;overflow:hidden;border:1px solid #ccc;background-color:#f1f1f1}
#pf_titleTab li{width:50%;height:61px;float:left}
#pf_titleTab li a{width:100%;height:61px;display:inline-block;color:#000;text-align:center;padding:5px 16px;text-decoration:none;transition:.3s;font-size:17px}
#pf_titleTab li a:hover{background-color:#ddd}
#pf_titleTab li a:focus,.active{background-color:#ccc}
#cardPaymentMethod{display:none;padding:6px 12px;border:1px solid #ccc;border-top:none}
#bkmPaymentMethod{display:none;border:1px solid #ccc}
#bkmPaymentMethod p{padding:8px;margin:0}
#payfull-card-holder{box-sizing:border-box;width:100%}
.pf_dates_div{margin-top: 20px}
#pf_month_select_div,#pf_year_select_div{float:left;width:49%;margin-right:1%}
#pf_month_p,#pf_year_p{width:100%}
#installment_table_id{width:100%;padding:10px;margin-top:20px;background-color:#eee;border:1px solid;border-radius:5px}
.install_head_label{float:left;font-weight:700;text-align:center;width:30%;height:40px;line-height:40px;border-bottom:2px solid #d2d2d2}
.add_space{width:10%;height:40px;text-align:center;line-height:40px}
.checkout-form-line{clear:both}
.install_body_label.installment_radio{width:10%;height:40px;text-align:center;line-height:40px}
.install_body_label{float:left;width:30%;height:40px;text-align:center;border-bottom:1px solid #d2d2d2;line-height:40px}
.installment_body,.installment_footer{clear:both}
#pf_submit_div{width:100%;text-align:right;margin-top:15px}
#pf_submit{margin:0;padding:1em 2em;display:inline-block;background-color:#222;border:0;border-radius:2px;box-shadow:none;color:#fff;cursor:pointer;font-weight:800;line-height:1;text-shadow:none;transition:background .2s;-webkit-appearance:button;overflow:visible;box-sizing:inherit;align-items:flex-start;text-align:center;user-select:none;white-space:pre;letter-spacing:normal;word-spacing:normal;text-transform:none;text-indent:0}
.bkmImage{height:100%!important}
.bkmTab{padding:2px!important}
.formTitle{margin-bottom:14px;border:2px solid #ccc;padding:8px;background-color:#ccc}
#titleText{margin:0 auto;padding:0;display:inline-block;vertical-align:middle;font-size:19px}
#payfullImageOnly{vertical-align:middle;display: inline-block}
#pf_cc_number_label,#pf_cvc_label{display: block}
#payfull-card-number,#payfull-card-cvc{width: 100%}
</style>