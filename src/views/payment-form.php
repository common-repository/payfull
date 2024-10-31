<?php

/* @vat $this the instance of WC_Gateway_Payfull */
wp_enqueue_script( 'wc-credit-card-form' );
$currency       = $currency_symbol;
$grandTotal     = $order->get_total();
$currencyAsText = $order->get_currency();
$bankImagesPath = plugins_url( 'images/', __FILE__ );

$IDS = [
    'bank'          => "{$id}-bank",
    'gateway'       => "{$id}-gateway",
    'cardset'       => "{$id}-cardset",
    'holder'        => "{$id}-card-holder",
    'pan'           => "{$id}-card-number",
    'month'         => "{$id}-card-month",
    'year'          => "{$id}-card-year",
    'cvc'           => "{$id}-card-cvc",
    'use3d-label'   => "{$id}-use3d-label",
    'use3d'         => "{$id}-use3d",
    'installment'   => "{$id}-installment",
    'use3d-row'     => "{$id}-use3d-row",
];

$LBLS = [
    'holder'        => __( 'Holder Name', 'payfull' ),
    'pan'           => __( 'Credit Card Number', 'payfull' ),
    'month'         => __( 'Expiry Month', 'payfull' ),
    'year'          => __( 'Expiry Year', 'payfull' ),
    'cvc'           => __( 'Card Verification Code (CVC)', 'payfull' ),
    'use3d'         => __( 'Use 3D secure Payments System', 'payfull' ),
    'installment'   => __("installment", "payfull"),
    'total'         => __("Total", "payfull"),
];

$VALS = [
    'bank'          => isset($form['bank']) ? $form['bank'] : '',
    'gateway'       => isset($form['gateway']) ? $form['gateway'] : '',
    'holder'        => isset($form['card']['holder']) ? $form['card']['holder'] : '',
    'pan'           => isset($form['card']['pan']) ? $form['card']['pan'] : '',
    'year'          => isset($form['card']['year']) ? $form['card']['year'] : '',
    'month'         => isset($form['card']['month']) ? $form['card']['month'] : '',
    'cvc'           => isset($form['card']['cvc']) ? $form['card']['cvc'] : '',
    'installment'   => isset($form["payment"]['installment']) ? $form["payment"]['installment'] : 1,
    'use3d'         => isset($form['use3d'])AND$form['use3d'] ? $form['use3d'] : 0,
    'campaign_id'   => isset($form['campaign_id'])AND$form['campaign_id'] ? $form['campaign_id'] : 0,
];


?>

<form method="post" class="col-md-12 payfull-checkout-form" id="pf_window">
    <div class="fieldset" id="<?php echo $IDS['cardset']; ?>">
        <?php if($enable_bkm) { ?>
            <ul class="tab" id="pf_titleTab">
                <li>
                    <a href="javascript:void(0)" class="tablinks active"
                       data-method="cardPaymentMethod"><?php echo __('Credit card/Debit card', 'payfull'); ?>
                        <img id="payfullImage" src="<?php echo $bankImagesPath; ?>logo.png">
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="tablinks bkmTab"
                       data-method="bkmPaymentMethod">
                        <img class="bkmImage" id="bkmLogo" src="<?php echo $bankImagesPath; ?>/BKM.png">
                    </a>
                </li>
            </ul>
        <?php } else { ?>
            <div class="formTitle">
                <p id="titleText"><?php echo __('Credit card/Debit card', 'payfull'); ?>
                    <img id="payfullImageOnly" src="<?php echo $bankImagesPath; ?>logo.png">
                </p>
            </div>
        <?php } ?>
        <?php if($enable_bkm) { ?>
        <div class="tabcontent" id="cardPaymentMethod" style="display: block;">
            <?php } ?>
            <p class="checkout-form-line">
                <label for="<?php echo $IDS['holder']; ?>"><?php echo $LBLS['holder']; ?>
                    <span class="required">*</span>
                </label>
                <input id="<?php echo $IDS['holder']; ?>" value="<?php echo $VALS['holder']; ?>"
                       class="input-text wc-credit-card-form-card-holder" type="text"
                       maxlength="20" autocomplete="off" placeholder="" name="card[holder]" />
            </p>
            <p class="checkout-form-line">
                <label id="pf_cc_number_label" for="<?php echo $IDS['pan']; ?>"><?php echo $LBLS['pan']; ?>
                    <span class="required">*</span>
                </label>
                <input value="<?php echo $VALS['pan']; ?>" id="<?php echo $IDS['pan']; ?>"
                       data-value="<?php echo $VALS['pan']; ?>"
                       class="input-text wc-credit-card-form-card-number input-cc-number-not-supported"
                       type="text" maxlength="20" autocomplete="off"
                       placeholder="•••• •••• •••• ••••" name="card[pan]" />
            </p>
            <div class="checkout-form-line">
                <div class="pf_dates_div" id="pf_month_select_div">
                    <p class="form-row form-row-first" id="pf_month_p">
                        <label for="<?php echo $IDS['month']; ?>"><?php echo $LBLS['month']; ?>
                            <span class="required">*</span>
                        </label>
                        <select id="<?php echo $IDS['month']; ?>" name="card[month]" class="input-text wc-credit-card-form-card-month">
                            <option value=""><?php echo __('Month', 'payfull'); ?></option>
                            <?php for($i=1;$i<=12;$i++) { ?>
                                <?php $i = (strlen($i) == 2)?$i:'0'.$i; ?>
                                <?php $selected = $i==$VALS['month'] ? 'selected' : ''; ?>
                                <option value="<?php echo $i;?>" <?php echo $selected; ?>><?php echo $i;?></option>
                            <?php } ?>
                        </select>
                    </p>
                </div>
                <div class="pf_dates_div" id="pf_month_select_div">
                    <p class="form-row form-row-last" id="pf_year_p">
                        <label for="<?php echo $IDS['year']; ?>"><?php echo $LBLS['year']; ?>
                            <span class="required">*</span>
                        </label>
                        <select id="<?php echo $IDS['year']; ?>" name="card[year]" class="input-text wc-credit-card-form-card-year">
                            <option value=""><?php echo __('Year', 'payfull'); ?></option>
                            <?php for($i=0;$i<15;$i++) : ?>
                                <?php $year = date('Y') + $i; ?>
                                <?php $selected = $year==$VALS['year'] ? 'selected' : ''; ?>
                                <option value="<?php echo $year;?>" <?php echo $selected; ?> ><?php echo $year;?></option>
                            <?php endfor; ?>
                        </select>
                    </p>
                </div>
            </div>
            <p class="checkout-form-line">
                <label id="pf_cvc_label" for="<?php echo $IDS['cvc']; ?>"><?php echo $LBLS['cvc']; ?>
                    <span class="required">*</span>
                </label>
                <input id="<?php echo $IDS['cvc']; ?>" value="<?php echo $VALS['cvc']; ?>"
                       class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off"
                       placeholder="CVC" name="card[cvc]" />
            </p>
            <?php if($enable_installment) { ?>
                <p class="form-row installment">
                <div id="installment_table_id">
                    <div class="installmet_head">
                        <div class="install_head_label add_space">
                            <img style="display: none" class="bank_photo" data-src="<?php echo $bankImagesPath; ?>" src="">
                        </div>
                        <div class="install_head_label"><?php echo __('Installment', 'payfull') ?></div>
                        <div class="install_head_label"><?php echo __('Amount / Month', 'payfull') ?></div>
                        <div class="install_head_label"><?php echo __('Total', 'payfull') ?></div>
                    </div>
                    <div class="installment_body" id="installment_body">
                        <div class="installment_row">
                            <div class="install_body_label installment_radio">
                                <input rel="1" type="radio" class="installment_radio"
                                       checked name="payment[installment]" value="1" />
                            </div>
                            <div class="install_body_label installment_lable_code">1</div>
                            <div class="install_body_label"><?php echo $currency.' '.$grandTotal; ?></div>
                            <div class="install_body_label final_commi_price"
                                 rel="<?php echo $grandTotal; ?>"><?php echo $currency.' '.$grandTotal; ?>
                            </div>
                        </div>
                    </div>
                    <div class="installment_footer"></div>
                </div>
                <input id="<?php echo $IDS['bank']; ?>" type="hidden" name="bank"
                       value="<?php echo $VALS['bank']; ?>" />
                <input id="<?php echo $IDS['gateway']; ?>" type="hidden" name="gateway"
                       value="<?php echo $VALS['gateway']; ?>" />
                <input id="<?php echo $IDS['installment']; ?>" type="hidden" name="installment"
                       value="<?php echo $VALS['installment']; ?>" />
                </p>
            <?php } ?>
            <?php if($enable_extra_installment) { ?>
                <div class="checkout-form-line extra_installments_container" style="display: none">
                    <p class="form-row form-row-first" >
                        <label><?php echo __('Extra Installmets', 'payfull') ?></label>
                    </p>
                    <div class="clear"></div>
                    <div class="extra_installments_select form-row-first"></div>
                </div>
            <?php } ?>
            <?php if($force_3dSecure) { ?>
                <p class="checkout-form-line payfull-3dsecure" id="<?php echo $IDS['use3d-row'] ?>">
                    <label for="<?php echo $IDS['use3d']; ?>">
                        <input data-forced="true" checked="checked" disabled="disabled"
                               id="<?php echo $IDS['use3d']; ?>" class="input-checkbox payfull-options-use3d"
                               type="checkbox" name="use3d" value="true" />
                        <?php echo $LBLS['use3d']; ?>
                    </label>
                </p>
            <?php } elseif($enable_3dSecure) { ?>
                <p class="checkout-form-line payfull-3dsecure" id="<?php echo $IDS['use3d-row'] ?>">
                    <label for="<?php echo $IDS['use3d']; ?>">
                        <input data-forced="false" <?php if(isset($VALS['use3d'])AND$VALS['use3d']) echo 'checked'; ?>
                               id="<?php echo $IDS['use3d']; ?>" class="input-checkbox payfull-options-use3d"
                               type="checkbox" name="use3d" value="true" />
                        <?php echo $LBLS['use3d']; ?>
                    </label>
                </p>
            <?php } ?>
            <?php if($enable_bkm) {?>
        </div>
        <div class="tabcontent" id="bkmPaymentMethod">
            <p> <?php echo __(' BKM Express ile ödeme yaparken www.bkmexpress.com.tr sayfasına yönlendirileceksiniz.
                        BKM Express sitesine üye olurken kullandığınız kullanıcı adı ve şifreniz ile uygulamaya giriş yapmanız gerekmektedir.
                        Karşınıza gelen ödeme ekranından işlem yapmak istediğiniz kartı ve ödeme şeklini seçerek kolayca ödeme yapabilirsiniz.
                        Alışverişinizi tamamladıktan sonra otomatik olarak sitemize yönlendirileceksiniz.'); ?>
            </p>
            <input id="useBKM" name="useBKM" type="hidden" value="0" />
        </div>
    <?php }?>
        <div class="clear"></div>
        <div id="pf_submit_div">
            <input type="submit" value="<?php echo __( 'Checkout', 'payfull' ); ?>" id="pf_submit">
        </div><br><br>
    </div>
</form>

<?php if($enable_installment) { ?>
    <script type="text/javascript">
        (function ($) {
            window.payfull = {
                bin: false,
                banks: [],
                total: parseFloat('<?php echo $grandTotal;?>'),
                currency: "<?php echo $currency;?>",
                totalSelector: "<?php echo $total_selector;?>",
                currencyClass: "<?php echo $currency_class;?>",
                oneShotCommission: 0,

                loadBanks: function() {
                    $.ajax({
                        url: "index.php?payfull-api=v1",
                        method: "POST",
                        data: { command:"banks" , total: payfull.total,
                            currency:'<?php echo $currencyAsText; ?>',
                            getExtraInstallmentsActive:'<?php echo ($enable_extra_installment)?'1':'0'; ?>'},
                        dataType: "json",

                        success: function (response) {
                            payfull.banks = response.data;
                            payfull.oneShotCommission = response.oneShotCommission;

                            <?php if(!empty($VALS['bank'])) { ?>
                            payfull.refreshInstallmentPlans("<?php echo $VALS['bank']; ?>");
                            <?php } ?>
                        }
                    });
                },

                updateGrandTotal: function(total, currency) {
                    total = Math.round(total * 100) / 100;
                    $(this.totalSelector).html('<span clas="'+this.currencyClass+'">'+currency+'</span>&nbsp;'+total);
                },

                detectCardBrand: function($el) {
                    var number = $el.val();
                    $el.removeClass('input-cc-number-not-supported');
                    var re_visa = new RegExp("^4");
                    var re_master = new RegExp("^5[1-5]");

                    if (number.match(re_visa) != null){
                        $el.addClass('input-cc-number-visa');
                        $el.removeClass('input-cc-number-master');
                    } else if (number.match(re_master) != null){
                        $el.removeClass('input-cc-number-visa');
                        $el.addClass('input-cc-number-master');
                    } else{
                        $el.removeClass('input-cc-number-visa');
                        $el.removeClass('input-cc-number-master');
                        $el.addClass('input-cc-number-not-supported');
                    }
                },

                onCardChanged: function (element) {
                    var $bank_photo = $('.bank_photo');
                    payfull.getExtraInstallments(1, 1, '', '');
                    this.detectCardBrand($(element));
                    var bin = $(element).val().replace(/\s/g, '').substr(0, 6);
                    if (bin.length < 6) {
                        payfull.refreshInstallmentPlans('', '', '', '');
                        $bank_photo.hide();
                        this.bin = bin;
                        return;
                    }
                    if (bin == this.bin) { return; }
                    this.bin = bin;

                    var url = "index.php?payfull-api=v1";
                    $.ajax({
                        url: url,
                        method: "POST",
                        data: { command:"bin", bin: bin },
                        dataType: "json",
                        success: function (response) {

                            var bank = response.data.bank_id;
                            var networkOrigin = response.data.bankAcceptInstallments.origin;
                            var networkMembers = response.data.bankAcceptInstallments.network;

                            payfull.refreshInstallmentPlans(bank, response.data.type, networkOrigin, networkMembers);

                            //force 3d for debit
                            if(response.data.type != 'CREDIT'){
                                $('#<?php echo $IDS['use3d']; ?>').attr('disabled', 'disabled');
                                $('#<?php echo $IDS['use3d']; ?>').prop("checked", true);

                            }else if($('#<?php echo $IDS['use3d']; ?>').attr('data-forced') == 'false'){
                                $('#<?php echo $IDS['use3d']; ?>').removeAttr('disabled');
                            }

                            //show bank image
                            if(bank && bank.length){
                                if(response.data.type == 'CREDIT'){
                                    $bank_photo.attr('src', $bank_photo.attr('data-src')+'networks/'+networkOrigin+'.png');
                                }else{
                                    $bank_photo.attr('src', $bank_photo.attr('data-src')+'banks/'+bank+'.png');
                                }
                                $bank_photo.show();
                            }else{
                                $bank_photo.hide();
                            }
                        }
                    });
                },

                show3D: function (val) {
                    <?php if($enable_3dSecure) { ?>
                    val ? $('#<?php echo $IDS['use3d-row']; ?>').show() : $('#<?php echo $IDS['use3d-row'] ?>').hide();
                    if (!val) {
                        $('#<?php echo $IDS['use3d-row'] ?> input[type="checkbox"]').prop("checked", false);
                        $('#<?php echo $IDS['use3d-row'] ?> label').removeClass("checked");
                    }
                    <?php } ?>
                },

                payWithInstallment: function (count, bank, gateway) {
                    $('#<?php echo $IDS['installment'] ?>').val(count);
                    $('#<?php echo $IDS['bank'] ?>').val(bank);
                    $('#<?php echo $IDS['gateway'] ?>').val(gateway);
                },

                payOneShot: function () {
                    this.show3D(true);
                    this.payWithInstallment(1, '', '');
                },

                getInstallmentOption: function(count, amount, percentage, currency, has3d, bank, gateway, hasExtra) {
                    var commission = percentage;//percentage.replace('%', '');
                    var fee = amount * parseFloat(commission) / 100;
                    var total = amount * (1 + parseFloat(commission) / 100);
                    var pmon = total / count;
                    var checked = count==1 ? 'checked' : '';

                    if(count == '<?php echo $VALS['installment']?>'){
                        if(gateway != '') payfull.getExtraInstallments(total, count, bank, gateway);
                        checked = 'checked';
                        payfull.updateGrandTotal(total, payfull.currency);
                    }

                    var textOfCount = count==1 ? '<?php echo __('One Shot', 'payfull')?>' : count;
                    if(' <?php echo $enable_extra_installment; ?>' == true){
                        textOfCount     = hasExtra=='1'?'<span class="joker">'+count+' + Joker</span>' : textOfCount;
                    }

                    return ''
                        + '<div class="installment_row">'
                        + '<div class="install_body_label installment_radio">'
                        + '<input rel="'+count+'" data-fee="'+fee.toFixed(2)+'" '
                        + 'data-total="'+total.toFixed(2)+'" data-has3d="'+has3d+'" '
                        + 'data-bank="'+bank+'" data-gateway="'+gateway+'" '
                        + 'class="custom_field_installment_radio" type="radio" '+checked+' name="payment[installment]" value="'+count+'" />'
                        + '</div>'
                        + '<div class="install_body_label installment_lable_code">'+textOfCount+'</div>'
                        + '<div class="install_body_label">' + currency +' '+ pmon.toFixed(2) + '</div>'
                        + '<div rel="' + total + '" class="install_body_label final_commi_price">' + currency +' '+ parseFloat(total).toFixed(2) + '</div>'
                        + '</div>'
                        ;
                },

                refreshInstallmentPlans: function (bankName, cardType, networkOrigin, networkMembers) {
                    this.payOneShot();

                    var $e = $('#installment_body');
                    $e.empty();
                    var optEl = this.getInstallmentOption(1, this.total, payfull.oneShotCommission, this.currency, 1, '', '');
                    $e.append(optEl);

                    if(cardType != 'CREDIT'){
                        return;
                    }

                    var origin        = 'false';
                    var cardIssuer    = 'false';
                    var member        = 'false';

                    for (var i in this.banks) {
                        var bank = this.banks[i];
                        if(bank.bank == networkOrigin){
                            origin = bank;
                            break;
                        } else if (bank.bank == bankName) {
                            cardIssuer = bank;
                        } else {
                            for (var b in networkMembers){
                                var members = networkMembers[b];
                                if(members == bankName){
                                    continue;
                                } else if (bank.bank == members) {
                                    member = bank;
                                    break;
                                }
                            }
                        }
                    }

                    if(origin != 'false') {
                        bank = origin;
                    } else if (cardIssuer != 'false') {
                        bank = cardIssuer;
                    } else if (member != 'false') {
                        bank = member;
                    } else {
                        bank.installments = [];
                    }

                    if (bank) {
                        var opt, t, fee;

                        for (var j in bank.installments) {
                            opt = bank.installments[j];
                            if(opt.count < 2) continue;

                            fee = parseFloat(opt.commission);
                            t = Math.round(this.total * (1+fee)*100)/100;
                            optEl = this.getInstallmentOption(opt.count, this.total, fee, this.currency, bank.has3d, bank.bank, bank.gateway, opt.hasExtra) ;
                            $e.append(optEl);
                        }
                    } else
                        alert("Banka bulunamadı.");

                },

                getExtraInstallments: function (total, count, bank, gateway) {
                    var divSelectorExtraInst  = $('.extra_installments_container');
                    var containerSelectorInst = $('.extra_installments_select');
                    if(count == '1'){
                        containerSelectorInst.html('');
                        divSelectorExtraInst.css('display', 'none');
                        return;
                    }
                    var url = "index.php?payfull-api=v1";
                    $.ajax({
                        url: url,
                        method: "POST",
                        data: { command:"extra_ins", total: total, currency:'<?php echo $currencyAsText; ?>', count:count, bank:bank, gateway:gateway},
                        dataType: "json",
                        success: function (response) {
                            var campaigns = response.data.campaigns;
                            if(campaigns){
                                var selectExtraInstallments = "<select name='campaign_id' class='form-control'>";
                                selectExtraInstallments = selectExtraInstallments+'<option value=""><?php echo __('- Select -');?></option>';
                                $.each(campaigns, function( index, value ) {
                                    var selected = '<?php echo $VALS['campaign_id']; ?>' == value.campaign_id ? 'selected' : '';
                                    var option   = '<option '+selected+' value="'+value.campaign_id+'">+ '+value.extra_installments+'</option>';
                                    selectExtraInstallments = selectExtraInstallments+option;
                                });
                                selectExtraInstallments = selectExtraInstallments+'</select>';
                                containerSelectorInst.html(selectExtraInstallments);
                                divSelectorExtraInst.css('display', 'block');
                            }else{
                                containerSelectorInst.html('');
                                divSelectorExtraInst.css('display', 'none');
                            }

                        }
                    });
                },

                run: function () {
                    this.loadBanks();
                    this.detectCardBrand($('#<?php echo $IDS['pan'] ?>'));
                    payfull.onCardChanged($('#<?php echo $IDS['pan'] ?>'));

                    $('#<?php echo $IDS['pan'] ?>').keyup(function () {
                        payfull.onCardChanged(this);
                    });

                    $('body').on("change", '.custom_field_installment_radio', function () {
                        var $el = $(this);
                        var count = $el.attr('rel');
                        var total = $el.data('total');

                        payfull.updateGrandTotal(total, payfull.currency);
                        payfull.getExtraInstallments(total, count, $el.data('bank'), $el.data('gateway'));

                        if(count!=1) {
                            payfull.show3D($el.data('has3d'));
                            payfull.payWithInstallment(count, $el.data('bank'), $el.data('gateway'));
                        } else {
                            payfull.payOneShot();
                        }
                    });

                    if (this.init) {
                        this.init();
                    }
                }
            };

        })(jQuery);
    </script>
<?php } else { ?>
    <script type="text/javascript">
        (function ($) {
            window.payfull = {
                bin: false,
                banks: [],
                total: parseFloat('<?php echo $grandTotal;?>'),
                currency: "<?php echo $currency;?>",
                totalSelector: "<?php echo $total_selector;?>",
                currencyClass: "<?php echo $currency_class;?>",

                detectCardBrand: function($el) {
                    var number = $el.val();
                    $el.removeClass('input-cc-number-not-supported');
                    var re_visa = new RegExp("^4");
                    var re_master = new RegExp("^5[1-5]");

                    if (number.match(re_visa) != null){
                        $el.addClass('input-cc-number-visa');
                        $el.removeClass('input-cc-number-master');
                    } else if (number.match(re_master) != null){
                        $el.removeClass('input-cc-number-visa');
                        $el.addClass('input-cc-number-master');
                    } else{
                        $el.removeClass('input-cc-number-visa');
                        $el.removeClass('input-cc-number-master');
                        $el.addClass('input-cc-number-not-supported');
                    }
                },

                onCardChanged: function (element) {
                    var $bank_photo = $('.bank_photo');
                    this.detectCardBrand($(element));
                    var bin = $(element).val().replace(/\s/g, '').substr(0, 6);
                    if (bin.length < 6) {
                        return;
                    }
                    if (bin == this.bin) { return; }
                    this.bin = bin;

                    var url = "index.php?payfull-api=v1";
                    $.ajax({
                        url: url,
                        method: "POST",
                        data: { command:"bin", bin: bin },
                        dataType: "json",
                        success: function (response) {
                            var bank = response.data.bank_id;
                            if (bank) {
                                payfull.refreshInstallmentPlans(bank, response.data.type, networkOrigin, networkMembers);
                            }

                            if(bank && bank.length){
                                if(response.data.type == 'CREDIT'){
                                    $bank_photo.attr('src', $bank_photo.attr('data-src')+'networks/'+bank+'.png');
                                }else{
                                    $bank_photo.attr('src', $bank_photo.attr('data-src')+'banks/'+bank+'.png');
                                }

                                $bank_photo.show();
                            }else{
                                $bank_photo.hide();
                            }
                        }
                    });
                },

                show3D: function (val) {
                    <?php if($enable_3dSecure) { ?>
                    val ? $('#<?php echo $IDS['use3d-row']; ?>').show() : $('#<?php echo $IDS['use3d-row'] ?>').hide();
                    if (!val) {
                        $('#<?php echo $IDS['use3d-row'] ?> input[type="checkbox"]').prop("checked", false);
                        $('#<?php echo $IDS['use3d-row'] ?> label').removeClass("checked");
                    }
                    <?php } ?>
                },

                payOneShot: function () {
                    this.show3D(true);
                    this.payWithInstallment(1, '', '');
                },

                refreshInstallmentPlans: function (bankName, cardType, networkOrigin, networkMembers) {
                },

                run: function () {
                    this.detectCardBrand($('#<?php echo $IDS['pan'] ?>'));

                    $('#<?php echo $IDS['pan'] ?>').keyup(function () {
                        payfull.onCardChanged(this);
                    });

                    $('body').on("change", '.custom_field_installment_radio', function () {
                        var $el = $(this);
                        var count = $el.attr('rel');
                        var total = $el.data('total');

                        payfull.updateGrandTotal(total, payfull.currency);
                        payfull.payOneShot();

                    });

                    if (this.init) {
                        this.init();
                    }

//                    $('#cardPaymentMethodTab').click(function(){ payfull.openPaymentMethod($(this), 'cardPaymentMethod'); });
//                    $('#bkmPaymentMethodTab').click(function(){ payfull.openPaymentMethod($(this), 'bkmPaymentMethod'); });
                }
            };
        })(jQuery);
    </script>
<?php } ?>

<script type="text/javascript">
    (function ($) {
        payfull.run();
    })(jQuery);
</script>

<script type="text/javascript">
    (function ($) {
        $('.tablinks').click(function(evt){
            methodName = $(this).attr('data-method');
            // Declare all variables
            var i, tabcontent, tablinks;

            // Get all elements with class="tabcontent" and hide them
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the link that opened the tab
            document.getElementById(methodName).style.display = "block";
            evt.currentTarget.className += " active";
            if(methodName == 'bkmPaymentMethod'){
                $('#useBKM').val(1);
            }else{
                $('#useBKM').val(0);
            }

        });
    })(jQuery);
</script>

<?php $this->renderView(__DIR__."/card-brand.css.php");?>
