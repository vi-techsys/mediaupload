<?php
/*
Template Name: Treasury Report
*/
//get_header( ); 
include_once('userheader.php');
include_once('auth.php');
include_once('dbconfig.php');
include_once('mssconfig.php');
?>
<script type="text/javascript" src="<?= get_template_directory_uri() . '/js/combodate.js' ?>"></script>
<!-- Include Moment.js CDN -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.3/moment.min.js">
</script>
<!-- Primary header -->
<style>
    label {
        color: white;
    }

    td {
        font-weight: bolder;
    }

    h3 {
        color: white;
    }
</style>
<section id="client">
    <div class="container">
        <header class="masthead">
            <div class="container px-5">
                <div class="row gx-5 align-items-center">
                    <center>
                        <h3 style="margin-top:20px;">Treasury Report</h3>
                    </center>
                    <form class=" form-horizontal" action="" method="POST">
                        <div style="color:white;">
                            <input type="text" data-format="DD-MM-YYYY h:mm a" data-template="DD / MM / YYYY  hh:mm a" value="<?php echo isset($_POST['from']) ? $_POST['from'] : '' ?>" id="from" name="from" required /> :From<br><br>
                            <input type="text" data-format="DD-MM-YYYY h:mm a" data-template="DD / MM / YYYY  hh:mm a" value="<?php echo isset($_POST['to']) ? $_POST['to'] : '' ?>" id="to" name="to" required /> :To &nbsp;&nbsp;

                            <br><br><a role="button" class="bg-danger" style="color:white; border-radius:4px; padding:2px;" onclick="toggle()">Toggle Date</a><br>
                        </div><br>
                        <input class="apply" type="checkbox" id="apply_branch" checked name="apply_branch" value="yes"><span style="color:red; font-weight:bold;"> Apply Branch ?</span> <br>
                        <label for="branch">Branch</label>
                        <div class="form-floating mb-3">
                            <select style="width: 40vh;" class="" name="branch" id="branch" onchange="load_users()" required>
                                <option value="0">Select branch</option>
                                <?php
                                $stmt =  $mss_conn->prepare('SELECT * FROM BranT');
                                $stmt->execute();
                                $brts = $stmt->fetchAll();
                                $selected1 = '';
                                foreach ($brts as $b) {
                                    if (isset($_POST['branch'])) {
                                        if ($_POST['branch'] == $b['Brnom']) {
                                            // $selected1 = 'selected';
                                        }
                                    }
                                    echo '<option ' . $selected1 . ' value ="' . $b['Brnom'] . '">' . $b['BRname'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <input class="apply" type="checkbox" id="apply_user" name="apply_user" value="yes"> <span style="color:red; font-weight:bold;"> Apply User ?</span><br>
                        <label for="user">User</label>
                        <div class="form-floating mb-3">
                            <select style="width: 40vh;" class="" disabled name="user" id="user" required>
                                <option value="">Select user</option>
                            </select>
                        </div>
                        <input type="submit" name="submit" value="Submit" class="btn btn-md btn-success" />
                    </form>
                    <?php
                    $w_total = 0;
                    $d_total = 0;
                    $e_total = 0;
                    $s_total = 0;
                    $s_r_total = 0;
                    $cash_r_count = 0;
                    $cash_rt_r_count = 0;
                    $del_r_count = 0;
                    $cdr_r_count = 0;
                    $cdr_r_r_count = 0;
                    $del_i_c = 0;
                    $h_sales = 0;
                    $m_charge = 0;
                    $svr = 0;
                    $vat_value = 0;
                    $visa_sale = 0;
                    $payout_value = 0;
                    $payout_value2 = 0;
                    $payin_value = 0;
                    $payin_value2 = 0;
                    $tip_value = 0;
                    $tip_value2 = 0;
                    $reservation_value = 0;
                    $busy_table_count = 0;
                    $pilot_payout_value = 0;
                    $tobank_value = 0;
                    $frombank_value = 0;
                    $fromopentable = 0;
                    $employee_with = 0;
                    $employee_sal = 0;
                    $total = 0;
                    $previous_credit = 0;
                    //process form
                    $sql = '';
                    $branch_sql = "";
                    $user_sql = "";
                    if (isset($_POST['submit'])) {
                        //calculate previous credit if (isset($_POST["apply_branch"])) {
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = " and PerBRID = " . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = " and peruser =" . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $datefrom = new DateTime($_POST['from']);
                        $dateto = new DateTime($_POST['to']);
                        //withdrawal
                        $sql = 'SELECT sum(Personal.peramount) AS tot FROM Personal';
                        $sql .= " where perdate < '" . $datefrom->format('m/d/Y H:i:s') . "' and pertype = 0" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $withdrawal = $stmt->fetchAll();
                        $w_total = $withdrawal[0]['tot'];
                        //deposits
                        $sql = 'SELECT sum(Personal.peramount) AS tot FROM Personal';
                        $sql .= " where perdate < '" . $datefrom->format('m/d/Y H:i:s') . "' and pertype = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $deposit = $stmt->fetchAll();
                        $d_total = $deposit[0]['tot'];
                        //expenses
                        $sql = 'SELECT sum(ExpD.Examount) AS tot FROM ExpD';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ExBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and Euser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where Exdate < '" . $datefrom->format('m/d/Y H:i:s') . "' " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $expenses = $stmt->fetchAll();
                        $e_total = $expenses[0]['tot'];
                        echo $sql;
                        //sales
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        // $sql_r = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.salTax as tot3,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and saleRtype < 4 and salcustcode = 0 and salT =1 " . $branch_sql . $user_sql;
                        //  $sql_r .= " saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and saleRtype < 4 and salcustcode = 0 and salT =1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $sales = $stmt->fetchAll();
                        $s_total = $sales[0]['tot'] - $sales[0]['tot2'] - $sales[0]['tot3'];
                        //cash receipt count
                        /*$stmt = $mss_conn->prepare($sql_r);
                $stmt->execute();
                $cash_rcount = $stmt->fetchAll();
                $cash_r_count = count($cash_rcount);*/
                        //sales return
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        //  $sql_r = 'SELECT Sales.salepayd AS tot,Sales.salTax as tot3, Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 1 and salcustcode =0 and salT = 1 " . $branch_sql . $user_sql;
                        //  $sql_r .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 1 and salcustcode =0 and salT = 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $salesr = $stmt->fetchAll();
                        $s_r_total = $salesr[0]['tot'] - $salesr[0]['tot2'] - $salesr[0]['tot3'];
                        //cash return receipt count
                        /* $stmt = $mss_conn->prepare($sql_r);
                $stmt->execute();
                $cashrrc = $stmt->fetchAll();
                $cash_rt_r_count = count($cashrrc);*/
                        //delivery receipt count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate <'" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and saldelT >0 and salcustcode > 0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $del_receipt = $stmt->fetchAll();
                        $del_r_count = count($del_receipt);
                        //credit receipt count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saldelT >0 and salcustcode > 0 and salT = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $crd_receipt = $stmt->fetchAll();
                        $cdr_r_count = count($crd_receipt);
                        //Credit Return receipts count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "'  and saltype =1 and saldelT = 0 and saleRtype =1 and salcustcode >0 and salT = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $crd_r_receipt_ = $stmt->fetchAll();
                        $cdr_r_r_count = count($crd_r_receipt_);
                        //deleted items count
                        $sql = 'SELECT UnregT.URitprice as tot2,UnregT.URdate FROM UnregT';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and UrBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and URUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where URdate < '" . $datefrom->format('m/d/Y H:i:s') . "' " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $del_item = $stmt->fetchAll();
                        $del_i_c = count($del_item);
                        //hall sales
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and saleRtype = 4 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $hall_sales = $stmt->fetchAll();
                        $h_sales = $hall_sales[0]['tot'] - $hall_sales[0]['tot2'] - $hall_sales[0]['tot3'];
                        //minimum charge
                        $sql = 'SELECT sum(Sales.salmnmcrg) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and saleRtype = 4 and salcustcode =0 and salT =1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $min_charge = $stmt->fetchAll();
                        $m_charge = $min_charge[0]['tot'];
                        //service
                        $sql = 'SELECT sum(Sales.salsercost) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and salcustcode = 0 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $service = $stmt->fetchAll();
                        $svr = $service[0]['tot'];
                        //vat
                        $sql = 'SELECT sum(Sales.salTax) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and salcustcode = 0 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $vat = $stmt->fetchAll();
                        $vat_value = $vat[0]['tot'];
                        //delivery
                        $sql = 'SELECT sum(Sales.saldelcost) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype =0 and salcustcode <>0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $delivery = $stmt->fetchAll();
                        $delv = $delivery[0]['tot'];
                        //visa sales
                        $sql = 'SELECT sum(Sales.saltot) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype = 0 and salT = 3" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $visasales = $stmt->fetchAll();
                        $visa_sale = $visasales[0]['tot'];
                        //payout
                        $sql = 'SELECT sum(Supppay.rectot) AS tot FROM Supppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SpBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ptype = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payout = $stmt->fetchAll();
                        $payout_value = $payout[0]['tot'];
                        //payout2
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ptype = 3 and recnom <> -999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payout2 = $stmt->fetchAll();
                        $payout_value2 = $payout2[0]['tot'];
                        //payin/receipt
                        $sql = 'SELECT sum(Supppay.rectot) AS tot FROM Supppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SpBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ptype = 3 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payin = $stmt->fetchAll();
                        $payin_value = $payin[0]['tot'];
                        //payin2/receipt
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ptype = 2 and recnom <> -999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payin2 = $stmt->fetchAll();
                        $payin_value2 = $payin2[0]['tot'];
                        //tips
                        $sql = 'SELECT sum(Sales.saltbs) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where saldate < '" . $datefrom->format('m/d/Y H:i:s') . "' and saltype =0 and saleRtype =4 and salT = 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $tips = $stmt->fetchAll();
                        $tip_value = $tips[0]['tot'];
                        //tips2
                        $sql = 'SELECT sum(OtherTBS.OTval) AS tot FROM OtherTBS';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and OtbsBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and OTUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where OTtime < '" . $datefrom->format('m/d/Y H:i:s') . "' " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $tips2 = $stmt->fetchAll();
                        $tip_value2 = $tips2[0]['tot'];
                        //reservation
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ptype = 2 and recnom =-999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $reservation = $stmt->fetchAll();
                        $reservation_value = $reservation[0]['tot'];
                        //busy table count
                        $sql = 'SELECT Orders.oruid AS tot FROM Orders';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ORBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and oruid = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        //(ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $datefrom->format('m/d/Y H:i:s') . "')
                        $sql .= " where orstate <> 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $busytablecount = $stmt->fetchAll();
                        $busy_table_count = count($busytablecount);
                        /* foreach ($busytablecount as $btc) {
                    $busy_table_count += 1;
                }*/
                        //pilots payout
                        $sql = 'SELECT sum(PiPay.rectot) AS tot FROM PiPay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and PPayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and (ptype =0  or ptype = 3) " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $pilotspayout = $stmt->fetchAll();
                        $pilot_payout_value = $pilotspayout[0]['tot'];
                        //transfer from treasury to bank
                        $sql = 'SELECT sum(MTR.Tamount) AS tot FROM MTR';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and MTBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and tuID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where Ttime < '" . $datefrom->format('m/d/Y H:i:s') . "' and tft =0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $fromttbank = $stmt->fetchAll();
                        $tobank_value = $fromttbank[0]['tot'];
                        //from bank to treasury
                        $sql = 'SELECT sum(MTR.Tamount) AS tot FROM MTR';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and MTBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and tuID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where Ttime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ttt = 0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $frombtt = $stmt->fetchAll();
                        $frombank_value = $frombtt[0]['tot'];
                        // calcindopenpay($datefrom, $datefrom);
                        $sql = 'SELECT sum(OrdersDE.ordpayed) AS tot FROM OrdersDE';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ORdBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and ordUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ordtime < '" . $datefrom->format('m/d/Y H:i:s') . "' and ordstate <> 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $opentable = $stmt->fetchAll();
                        $fromopentable = $opentable[0]['tot'];
                        // getHR1($datefrom, $datefrom);
                        //employee withdrawal
                        $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and Ptype = 8 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $empwithd = $stmt->fetchAll();
                        $employee_with = $empwithd[0]['tot'];
                        //employee salaries
                        $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where ptime < '" . $datefrom->format('m/d/Y H:i:s') . "' and Ptype = 6 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $empsal = $stmt->fetchAll();
                        $employee_sal = $empsal[0]['tot'];
                        //calculate total
                        $previous_credit = ($d_total + $s_total + $h_sales + $m_charge + $payin_value + $payin_value2) - ($w_total + $e_total + $s_r_total + $payout_value + $payout_value2 + $employee_with + $employee_sal + $pilot_payout_value) + $svr + $vat_value + $tip_value + $tip_value2 + $reservation_value + $fromopentable;
                        /*  prev.Text = (Val(lblin.Text) + Val(sales.Text) + Val(Hallsales.Text) + Val(MNMLBL.Text) + Val(payin.Text)) - (Val(lblout.Text) + Val(lblEXP.Text) + Val(salesR.Text) + Val(payout.Text) + Val(EMPLone.Text) + Val(EMPSal.Text) + Val(LBLpiPay.Text))
            prev.Text = Val(prev.Text) + Val(ServLBL.Text) + Val(TaxLBL.Text) + Val(Lbltbs.Text) + Val(lblres.Text) + Val(LBLopentab.Text)
            prev.Text = (Val(prev.Text) - Val(TOBNK.Text)) + Val(FromBNK.Text)*/
                        $previous_credit = $previous_credit - $tobank_value + $frombank_value;

                        //calculate total and other values
                        $w_total = 0;
                        $d_total = 0;
                        $e_total = 0;
                        $s_total = 0;
                        $s_r_total = 0;
                        $cash_r_count = 0;
                        $cash_rt_r_count = 0;
                        $del_r_count = 0;
                        $cdr_r_count = 0;
                        $cdr_r_r_count = 0;
                        $del_i_c = 0;
                        $h_sales = 0;
                        $m_charge = 0;
                        $svr = 0;
                        $vat_value = 0;
                        $visa_sale = 0;
                        $payout_value = 0;
                        $payout_value2 = 0;
                        $payin_value = 0;
                        $payin_value2 = 0;
                        $tip_value = 0;
                        $tip_value2 = 0;
                        $reservation_value = 0;
                        $busy_table_count = 0;
                        $pilot_payout_value = 0;
                        $tobank_value = 0;
                        $frombank_value = 0;
                        $fromopentable = 0;
                        $employee_with = 0;
                        $employee_sal = 0;


                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = " and PerBRID = " . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = " and peruser =" . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $datefrom = new DateTime($_POST['from']);
                        $dateto = new DateTime($_POST['to']);
                        //withdrawal
                        $sql = 'SELECT sum(Personal.peramount) AS tot FROM Personal';
                        $sql .= " where (perdate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and perdate <= '" . $dateto->format('m/d/Y H:i:s') . "') and pertype = 0" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $withdrawal = $stmt->fetchAll();
                        $w_total = $withdrawal[0]['tot'];
                        //deposits
                        $sql = 'SELECT sum(Personal.peramount) AS tot FROM Personal';
                        $sql .= " where (perdate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and perdate <= '" . $dateto->format('m/d/Y H:i:s') . "') and pertype = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $deposit = $stmt->fetchAll();
                        $d_total = $deposit[0]['tot'];
                        //expenses
                        $sql = 'SELECT sum(ExpD.Examount) AS tot FROM ExpD';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ExBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and Euser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (Exdate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and Exdate <=  '" . $dateto->format('m/d/Y H:i:s') . "') " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $expenses = $stmt->fetchAll();
                        $e_total = $expenses[0]['tot'];
                        //sales
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        $sql_r = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.salTax as tot3,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and saleRtype < 4 and salcustcode = 0 and salT =1 " . $branch_sql . $user_sql;
                        $sql_r .= " where (saldate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and saleRtype < 4 and salcustcode = 0 and salT =1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $sales = $stmt->fetchAll();
                        $s_total = $sales[0]['tot'] - $sales[0]['tot2'] - $sales[0]['tot3'];
                        //cash receipt count
                        $stmt = $mss_conn->prepare($sql_r);
                        $stmt->execute();
                        $cash_rcount = $stmt->fetchAll();
                        $cash_r_count = count($cash_rcount);
                        //sales return
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        $sql_r = 'SELECT Sales.salepayd AS tot,Sales.salTax as tot3, Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 1 and salcustcode =0 and salT = 1 " . $branch_sql . $user_sql;
                        $sql_r .= " where (saldate >= '" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 1 and salcustcode =0 and salT = 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $salesr = $stmt->fetchAll();
                        $s_r_total = $salesr[0]['tot'] - $salesr[0]['tot2'] - $salesr[0]['tot3'];
                        //cash return receipt count
                        $stmt = $mss_conn->prepare($sql_r);
                        $stmt->execute();
                        $cashrrc = $stmt->fetchAll();
                        $cash_rt_r_count = count($cashrrc);
                        //delivery receipt count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and saldelT >0 and salcustcode > 0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $del_receipt = $stmt->fetchAll();
                        $del_r_count = count($del_receipt);
                        //credit receipt count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "')  and saldelT =0 and salcustcode > 0 and salT = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $crd_receipt = $stmt->fetchAll();
                        $cdr_r_count = count($crd_receipt);
                        //Credit Return receipts count
                        $sql = 'SELECT Sales.salepayd AS tot,Sales.salsercost as tot2,Sales.saldate, Sales.saltype FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "')  and saltype =1 and saldelT = 0 and saleRtype =1 and salcustcode >0 and salT = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $crd_r_receipt_ = $stmt->fetchAll();
                        $cdr_r_r_count = count($crd_r_receipt_);
                        //deleted items count
                        $sql = 'SELECT UnregT.URitprice as tot2,UnregT.URdate FROM UnregT';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and UrBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and URUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (URdate >='" . $datefrom->format('m/d/Y H:i:s') . "' and URdate <= '" . $dateto->format('m/d/Y H:i:s') . "') " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $del_item = $stmt->fetchAll();
                        $del_i_c = count($del_item);
                        //hall sales
                        $sql = 'SELECT sum(Sales.salepayd) AS tot,sum(Sales.salsercost) AS tot2,sum(Sales.salTax) AS tot3 FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and saleRtype = 4 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $hall_sales = $stmt->fetchAll();
                        $h_sales = $hall_sales[0]['tot'] - $hall_sales[0]['tot2'] - $hall_sales[0]['tot3'];
                        //minimum charge
                        $sql = 'SELECT sum(Sales.salmnmcrg) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and saleRtype = 4 and salcustcode =0 and salT =1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $min_charge = $stmt->fetchAll();
                        $m_charge = $min_charge[0]['tot'];
                        //service
                        $sql = 'SELECT sum(Sales.salsercost) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and salcustcode = 0 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $service = $stmt->fetchAll();
                        $svr = $service[0]['tot'];
                        //vat
                        $sql = 'SELECT sum(Sales.salTax) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "') and saltype = 0 and salcustcode = 0 and salT = 1" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $vat = $stmt->fetchAll();
                        $vat_value = $vat[0]['tot'];
                        //delivery
                        $sql = 'SELECT sum(Sales.saldelcost) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "')  and saltype =0 and salcustcode <>0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $delivery = $stmt->fetchAll();
                        $delv = $delivery[0]['tot'];
                        //visa sales
                        $sql = 'SELECT sum(Sales.saltot) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser =' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "')   and saltype = 0 and salT = 3" . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $visasales = $stmt->fetchAll();
                        $visa_sale = $visasales[0]['tot'];
                        //payout
                        $sql = 'SELECT sum(Supppay.rectot) AS tot FROM Supppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SpBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "')   and ptype = 2 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payout = $stmt->fetchAll();
                        $payout_value = $payout[0]['tot'];
                        //payout2
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "')    and ptype = 3 and recnom <> -999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payout2 = $stmt->fetchAll();
                        $payout_value2 = $payout2[0]['tot'];
                        //payin/receipt
                        $sql = 'SELECT sum(Supppay.rectot) AS tot FROM Supppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SpBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "')  and ptype = 3 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payin = $stmt->fetchAll();
                        $payin_value = $payin[0]['tot'];
                        //payin2/receipt
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "')  and ptype = 2 and recnom <> -999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $payin2 = $stmt->fetchAll();
                        $payin_value2 = $payin2[0]['tot'];
                        //tips
                        $sql = 'SELECT sum(Sales.saltbs) AS tot FROM Sales';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and SalBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and saluser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (saldate >='" . $datefrom->format('m/d/Y H:i:s') . "' and saldate <= '" . $dateto->format('m/d/Y H:i:s') . "')  and saltype =0 and saleRtype =4 and salT = 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $tips = $stmt->fetchAll();
                        $tip_value = $tips[0]['tot'];
                        //tips2
                        $sql = 'SELECT sum(OtherTBS.OTval) AS tot FROM OtherTBS';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and OtbsBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and OTUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (OTtime >='" . $datefrom->format('m/d/Y H:i:s') . "' and OTtime <= '" . $dateto->format('m/d/Y H:i:s') . "') " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $tips2 = $stmt->fetchAll();
                        $tip_value2 = $tips2[0]['tot'];
                        //reservation
                        $sql = 'SELECT sum(custpay.rectot) AS tot FROM custpay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and BRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "') and ptype = 2 and recnom =-999 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $reservation = $stmt->fetchAll();
                        $reservation_value = $reservation[0]['tot'];
                        //busy table count
                        $sql = 'SELECT Orders.oruid AS tot FROM Orders';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ORBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and oruid = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        //(ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "')
                        $sql .= " where orstate <> 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $busytablecount = $stmt->fetchAll();
                        $busy_table_count = count($busytablecount);
                        /* foreach ($busytablecount as $btc) {
                    $busy_table_count += 1;
                }*/
                        //pilots payout
                        $sql = 'SELECT sum(PiPay.rectot) AS tot FROM PiPay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and PPayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "') and (ptype =0  or ptype = 3) " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $pilotspayout = $stmt->fetchAll();
                        $pilot_payout_value = $pilotspayout[0]['tot'];
                        //transfer from treasury to bank
                        $sql = 'SELECT sum(MTR.Tamount) AS tot FROM MTR';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and MTBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and tuID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (Ttime >='" . $datefrom->format('m/d/Y H:i:s') . "' and Ttime <= '" . $dateto->format('m/d/Y H:i:s') . "') and tft =0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $fromttbank = $stmt->fetchAll();
                        $tobank_value = $fromttbank[0]['tot'];
                        //from bank to treasury
                        $sql = 'SELECT sum(MTR.Tamount) AS tot FROM MTR';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and MTBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and tuID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (Ttime >='" . $datefrom->format('m/d/Y H:i:s') . "' and Ttime <= '" . $dateto->format('m/d/Y H:i:s') . "') and ttt = 0 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $frombtt = $stmt->fetchAll();
                        $frombank_value = $frombtt[0]['tot'];
                        // calcindopenpayold($dateto, $datefrom);
                        $sql = 'SELECT sum(OrdersDE.ordpayed) AS tot FROM OrdersDE';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and ORdBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and ordUID = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ordtime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ordtime <= '" . $dateto->format('m/d/Y H:i:s') . "') and ordstate <> 1 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $opentable = $stmt->fetchAll();
                        $fromopentable = $opentable[0]['tot'];
                        // getHR1($dateto, $datefrom);
                        //employee withdrawal
                        $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "') and Ptype = 8 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $empwithd = $stmt->fetchAll();
                        $employee_with = $empwithd[0]['tot'];
                        //employee salaries
                        $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                        if (isset($_POST["apply_branch"])) {
                            $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                        } else {
                            $branch_sql = '';
                        }
                        if (isset($_POST['apply_user'])) {
                            $user_sql = ' and puser = ' . $_POST['user'];
                        } else {
                            $user_sql = '';
                        }
                        $sql .= " where (ptime >='" . $datefrom->format('m/d/Y H:i:s') . "' and ptime <= '" . $dateto->format('m/d/Y H:i:s') . "') and Ptype = 6 " . $branch_sql . $user_sql;
                        $stmt = $mss_conn->prepare($sql);
                        $stmt->execute();
                        $empsal = $stmt->fetchAll();
                        $employee_sal = $empsal[0]['tot'];
                        //calculate total
                        $total = ($d_total + $s_total + $h_sales + $m_charge + $payin_value + $payin_value2) - ($w_total + $e_total + $s_r_total + $payout_value + $payout_value2 + $employee_with + $employee_sal + $pilot_payout_value) + $svr + $vat_value + $tip_value + $tip_value2 + $reservation_value + $fromopentable;
                        // total.Text = (Val(lblin.Text) + Val(sales.Text) + Val(Hallsales.Text) + Val(MNMLBL.Text) + Val(payin.Text)) - (Val(lblout.Text) + Val(lblEXP.Text) + Val(salesR.Text) + Val(payout.Text) + Val(EMPLone.Text) + Val(EMPSal.Text) + Val(LBLpiPay.Text))
                        //total.Text = Val(total.Text) + Val(ServLBL.Text) + Val(TaxLBL.Text) + Val(Lbltbs.Text) + Val(lblres.Text) + Val(LBLopentab.Text)
                        //total.Text = Format((Val(total.Text) - Val(TOBNK.Text)) + Val(FromBNK.Text), "0.00")
                        $total = $total - $frombank_value + ($tobank_value);
                        //table 
                    }
                    /*
            function calcindopenpayold($dt, $df)
            {
                include_once('mssconn.php');
                $sql = 'SELECT sum(OrdersDE.ordpayed) AS tot FROM OrdersDE';
                $user_sql = ' and ordUID = ' . $_POST['user'];
                $branch_sql = ' and ORdBRID =' . $_POST['branch'];
                $sql .= " where (ordtime >='" . $df->format('m/d/Y H:i:s') . "' and ordtime <= '" . $dt->format('m/d/Y H:i:s') . "') and ordstate <> 1 " . $branch_sql . $user_sql;
                $stmt = $mss_conn->prepare($sql);
                $stmt->execute();
                $opentable = $stmt->fetchAll();
                $fromopentable = $opentable[0]['tot'];
            }
            function  getHR1($dt, $df)
            {
                include_once('mssconn.php');
                //employee withdrawal
                $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                $user_sql = ' and puser = ' . $_POST['user'];
                $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                $sql .= " where (ptime >='" . $df->format('m/d/Y H:i:s') . "' and ptime <= '" . $dt->format('m/d/Y H:i:s') . "') and Ptype = 8 " . $branch_sql . $user_sql;
                $stmt = $mss_conn->prepare($sql);
                $stmt->execute();
                $empwithd = $stmt->fetchAll();
                $employee_with = $empwithd[0]['tot'];
                //employee salaries
                $sql = 'SELECT Sum(Emppay.rectot) AS SumOftot FROM Emppay';
                $user_sql = ' and puser = ' . $_POST['user'];
                $branch_sql = ' and EmpayBRID =' . $_POST['branch'];
                $sql .= " where (ptime >='" . $df->format('m/d/Y H:i:s') . "' and ptime <= '" . $dt->format('m/d/Y H:i:s') . "') and Ptype = 6 " . $branch_sql . $user_sql;
                $stmt = $mss_conn->prepare($sql);
                $stmt->execute();
                $empsal = $stmt->fetchAll();
                $employee_sal = $empsal[0]['tot'];
            }*/
                    ?>
                    <br><br><br>
                    <div class="col-lg-10 col-md-10 col-sm-12" style="overflow:auto;">
                        <h3>Treasury Report</h3>
                        <table class="table table-bordered table-secondary">
                            <tr>
                                <th></th>
                                <th width="20"></th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><?= $w_total == null ? 0 : number_format($w_total, 2) ?> &nbsp;: &nbsp;Withdrawals</td>
                                <td></td>
                                <td><?= $d_total == null ? 0 : number_format($d_total, 2) ?> &nbsp;: &nbsp;Deposits</td>
                            </tr>
                            <tr>
                                <td><?= $e_total == null ? 0 : number_format($e_total, 2) ?> &nbsp;: &nbsp;Expenses</td>
                                <td></td>
                                <td><?= $cash_r_count == null ? 0 : $cash_r_count ?> &nbsp;: &nbsp;Cash Receipt Count</td>
                            </tr>
                            <tr>
                                <td><?= $cash_rt_r_count = null ? 0 : $cash_rt_r_count  ?> &nbsp;: &nbsp;Cash Return Receipt Count</td>
                                <td></td>
                                <td><?= $frombank_value == null ? 0 : $frombank  ?> &nbsp;: &nbsp;Transfer from bank to treasury</td>
                            </tr>
                            <tr>
                                <td><?= $cdr_r_count   ?> &nbsp;: &nbsp;Credit Receipt Count</td>
                                <td></td>
                                <td><?= $tobank_value == null ? 0 : $tobank ?> &nbsp;: &nbsp;Transfer from treasury to bank</td>
                            </tr>
                            <tr>
                                <td><?= $cdr_r_r_count ?> &nbsp;: &nbsp;Credit Return Receipt Count</td>
                                <td></td>
                                <td><?= $s_total == null ? 0 : number_format($s_total, 2) ?> &nbsp;: &nbsp;Sales</td>
                            </tr>
                            <tr>
                                <td><?= $del_r_count  ?> &nbsp;: &nbsp;Delivery Receipt Count</td>
                                <td></td>
                                <td><?= $s_r_total == null ? 0 : number_format($s_r_total, 2) ?> &nbsp;: &nbsp;Sales Return</td>
                            </tr>
                            <tr>
                                <td><?= 0  ?> &nbsp;: &nbsp;Hall Receipt Count</td>
                                <td></td>
                                <td><?= $h_sales == null ? 0 : number_format($h_sales, 2) ?> &nbsp;: &nbsp;Hall Sales</td>
                            </tr>
                            <tr>
                                <td><?= $del_i_c  ?> &nbsp;: &nbsp;Deleted Items Count</td>
                                <td></td>
                                <td><?= $visa_sale == null ? 0 : number_format($visa_sale, 2) ?> &nbsp;: &nbsp;Visa Sales</td>
                            </tr>
                            <tr>
                                <td><?= 0  ?> &nbsp;: &nbsp;Visa Count</td>
                                <td></td>
                                <td><?= $svr == null ? 0 : number_format($svr, 2)  ?> &nbsp;: &nbsp;Service</td>
                            </tr>
                            <tr>
                                <td><?= $busy_table_count  ?> &nbsp;: &nbsp;Busy Table Count</td>
                                <td></td>
                                <td><?= $vat_value == null ? 0 : number_format($vat_value, 2) ?> &nbsp;: &nbsp;Vat</td>
                            </tr>
                            <tr>
                                <td><?= 0 ?> &nbsp;: &nbsp;Credit Sales Total</td>
                                <td></td>
                                <td><?= $delv == null ? 0 : number_format($delv, 2) ?> &nbsp;: &nbsp;Delivery</td>
                            </tr>
                            <tr>
                                <td><?= $m_charge == null ? 0 : number_format($m_charge, 2)  ?> &nbsp;: &nbsp;Minimum Charge</td>
                                <td></td>
                                <td><?= number_format($payout_value + $payout_value2, 2) ?> &nbsp;: &nbsp;Payments</td>
                            </tr>
                            <tr>
                                <td><?= number_format($payin_value + $payin_value2, 2) ?> &nbsp;: &nbsp;Receipts</td>
                                <td></td>
                                <td><?= $reservation_value == null ? 0 : number_format($reservation_value, 2) ?> &nbsp;: &nbsp;Reservation</td>
                            </tr>
                            <tr>
                                <td><?= number_format($tip_value + $tip_value2, 2) ?> &nbsp;: &nbsp;Tips</td>
                                <td></td>
                                <td><?= $fromopentable == null ? 0 : $fromopentable ?> &nbsp;: &nbsp;Payments from open table</td>
                            </tr>
                            <tr>
                                <td><?= $employee_with == null ? 0 : number_format($employee_with, 2) ?> &nbsp;: &nbsp;Employee Withdrawals</td>
                                <td></td>
                                <td><?= $employee_sal == null ? 0 : number_format($employee_sal, 2) ?> &nbsp;: &nbsp;Salaries</td>
                            </tr>
                            <tr>
                                <td><?= number_format($previous_credit, 2) ?> : Previous Credit</td>
                                <td></td>
                                <td><?= $pilot_payout_value == null ? 0 : number_format($pilot_payout_value, 2) ?> &nbsp;: &nbsp;Pilots' Payouts</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><?= number_format($total, 2) ?> &nbsp;: &nbsp;Total</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </header>

        <script>
            (function($) {
                $(document).ready(function() {
                    $('.apply').change(function() {
                        if ($(this).is(':checked')) {
                            if ($(this).attr('name') == 'apply_branch') {
                                $('#branch').removeAttr('disabled');
                            }
                            if ($(this).attr('name') == 'apply_user') {
                                $('#user').removeAttr('disabled');
                            }
                        } else {
                            if ($(this).attr('name') == 'apply_branch') {
                                $('#branch').attr('disabled', 'disabled');
                            }
                            if ($(this).attr('name') == 'apply_user') {
                                $('#user').attr('disabled', 'disbaled');
                            }
                        }
                    });
                    //set datetime
                    $('#from').combodate({
                        minYear: 2000,
                        maxYear: 2100,
                    });
                    $('#from').combodate('setValue', new Date());
                    $('#to').combodate({
                        minYear: 2000,
                        maxYear: 2100,
                    });
                    $('#to').combodate('setValue', new Date());
                });
            })(jQuery);
        </script>
    </div>
</section>
<?php
add_action('wp_footer', function () { ?>
    <script>
        function load_users() {
            (function($) {
                var branch = $('#branch').val();
                $.ajax({
                    url: "<?php bloginfo('url'); ?>/wp-content/themes/mobisilk/fetchusers.php",
                    method: "POST",
                    data: {
                        branch: branch,
                    },
                    success: function(response) {
                        $('#user').html(response);
                    },
                    error: function(err) {
                        alert(err);
                    }
                });
            })(jQuery);
        }

        function toggle() {
            (function($) {
                $('#from').toggle();
                $('#to').toggle();
                $('.combodate').toggle();
            })(jQuery);
        }
    </script>
<?php });
get_footer(); ?>