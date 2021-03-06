<!DOCTYPE html>

<?php
/***************************************************\
   account_main.php
   purpose: main center for account access, login,
     registration, withdrawals, payments.

\***************************************************/
?>

<?php session_start();?>

<html>
  <head>
    <title>BitWizard.com - Bitcoin Investment Training, Practice, and Planning</title>
    <link rel="stylesheet" type="text/css" href="/style.css">
  </head>
  <body>
    <script>var rclicked = false;</script>
    <div id="wrap">
      <div id="header"><?php include_once($_SERVER['DOCUMENT_ROOT'] . "/HEADER.php"); ?></div>
      <div id="nav">
        <?php include_once($_SERVER['DOCUMENT_ROOT'] . "/NAVBAR.php"); ?>
      </div>
      <div id="main">
        <!-- Debug links - TODO: remove them when finished -->
        <p>DEBUG LINKS ONLY</p>
        <a href="/Account/initiate_db.php">Initiate the Database</a><br />
        <a href="/Account/read_database.php">Read the Database</a><br />
        <a href="/Account/register_form.php">Registration Form</a><br /><br />
        <?php
          // Check to see if user is logged in (if so, info will exist in $_SESSION['user'])
          // The "cockblock" variable is a quick hack, so we don't have two login forms acting separately.
          if((!isset($_SESSION['user'])) && (!isset($cockblock))) {
            echo "You're not logged in! Please use the following form to log in:<br />\n";
            echo "<form action=\"/Account/login.php\" method=\"post\">\n";
            echo "  <input type='hidden' name='returnURL' value='" . $_SERVER['REQUEST_URI'] . "'>";
            echo "  <input type='text' style='width: 350px;' id='BTC_Address' name='BTC_Address' value='Bitcoin Address' onclick=\"if(false == rclicked) document.getElementById('BTC_Address').value=''; rclicked = true;\">\n";
            echo "  <input type='submit' value='Login'>";
            echo "</form>";
            echo "<br />OR just register on this form:<br /><br />";
            
            // User isn't logged in, it's possible they aren't registered. Here's the registration form:
            include($_SERVER['DOCUMENT_ROOT'] . "/Account/register_form.php");
          } else if(!isset($cockblock)) {
            // Here's the actual meat and potatoes of what you want to display...
            display_user_info();
          }
          
          // Function: Display User Info.
          // Purpose: Output to the user all of the account information they will need on this page.
          function display_user_info() {
            // Include our SQL global variables...
            include_once($_SERVER['DOCUMENT_ROOT'] . "/sql_includes.php");
            echo "<script>\n";
            echo "function confirmDelete() {\n";
            echo "  var bDelete = confirm(\"Delete Account and loose " . ($_SESSION['user']['Balance']) . " transferrable BTC ($" . ($_SESSION['user']['Balance'] * getCurrentBTCPrice()) . ")?\");\n";
            echo "  if(true == bDelete) {\n";
            echo "    document.getElementById(\"deleteAccountForm\").submit();\n";
            echo "  }\n";
            echo "}\n";
            echo "</script>";
            echo "User Information:<br /><br />\n";
            echo "Bitcoin Address:<i> " . $_SESSION['user']['ID'] . "</i><br />\n";
            echo "Account Balance: <i>" . number_format($_SESSION['user']['Total_Balance'], 6) . "</i><br />\n";
            echo "Transferrable Balance: <i>" . number_format($_SESSION['user']['Balance'], 6) . "</i><br />\n";
            echo "<form action='delete.php' id='deleteAccountForm' method='post'>\n";
            echo "<input type='hidden' name='Return_URL' value='" . $_SERVER['REQUEST_URI'] . "'>\n";
            echo "<input type='hidden' name='action' value='delete_account'>\n";
            echo "<input type='hidden' name='ID' value='" . $_SESSION['user']['ID'] . "'>\n";
            echo "</form>";
            echo "<input type='submit' value='Delete Account' onclick='confirmDelete();'>\n";
            echo "<hr>";
            
            // Get list of practice accounts. TODO: Make them links to the PracticeAccount bit!
            //  Right now it's just a quick hack, because it's just a quick hack to enumerate them.
            echo "<table style='width: 100%; text-align: center;'><tr><td><b>User Practice Accounts</b></td></tr>\n";
            echo "<tr><td><i>Click <a href='/Planning/portfolio_add_balance_form.php'>here</a> to add BTC to a portfolio</i></td></tr></table>\n";
            foreach($_SESSION['user']['PracticeAcct'] as $key => $value) {
              if($value != 0) {
                echo "<div style='border: 3px solid black'>\n";
                echo "<!--Form for cancelling transactions-->\n<form id='cancelForm' action='/Planning/bitcoin_trade.php' method='post'>\n";
                echo "<input type='hidden' name='action' value='cancel'>\n";
                echo "<input type='hidden' name='Return_URL' value='" . $_SERVER['REQUEST_URI'] . "'>\n";
                echo "<input type='hidden' name='ID_to_cancel' id='tcID' value=''>\n";
                echo "<input type='hidden' name='type' id='tcTYPE' value=''></form>\n";
                echo "<table width='100%'><tr>";
                echo "<td>Practice Account ID:</td><td>$key</td><td>\n";
                echo "<form action='delete.php' method='post'>\n";
                echo "<input type='hidden' name='Return_URL' value='" . $_SERVER['REQUEST_URI'] . "'>\n";
                echo "<input type='hidden' name='action' value='delete_portfolio'>\n";
                echo "<input type='hidden' name='ID' value='" . $key . "'>\n";
                echo "<input type='submit' value='Delete Portfolio'></form></td></tr><tr>\n";
                // TODO: Resume this system here. WARNING: The "login" system may have failed.
                echo "<td>Balance (USD)</td><td>$" . number_format($value['Balance_USD'], 6) . "</td>\n";
                echo "<td>Balance (BTC)</td><td>" . number_format($value['Balance_BTC'], 6) . "</td></tr><tr>\n";
                echo ($value['Shared'] == 'y') ? "<td>Visibility</td><td>Shared</td></tr><tr>\n" : "<td>Visibility</td><td>Private</td></tr><tr>\n";
                echo "<td>Value Increase<a href='/FAQ.php#q_vai'>*</a></td><td>" . number_format((($value['Value'] - $value['ValueIncrease']) / $value['ValueIncrease']) * 100, 2) . "%</td>\n";
                echo "<td><form action='/Planning/bitcoin_trade.php' method='post'>\n";
                echo "<input type='hidden' name='action' value='redeem'>\n";
                echo "<input type='hidden' name='portfolioID' value='" . $key . "'>\n";
                //echo "<input type='hidden' name='Return_URL' value='" . $_SERVER['REQUEST_URI'] . "'>\n";
                echo "<input type='submit' name='redeemIncrease' value='Redeem for BTC'></form></td></tr></table><br />\n";
                
                // Get pending transactions and history here... Using system from practice_main.php
                $pending = explode(",", $value['Pending']);
                $history = explode("-", $value['History']);
                echo "<table style='width: 100%; text-align: center'><tr><td>Pending Transactions</td></tr></table>\n";
                echo "<table style='width: 100%'>\n";
                foreach($pending as $PendingTransaction) {
                  if("B" == $PendingTransaction[0]) {
                    echo "<tr style='background-color: #E0FFFF;'><td width='10%'>"; $index++;
                    echo "Buy</td>\n";
                    if($tradeInfo = GetTradeInfo($_SERVER['DOCUMENT_ROOT'] . "/data/buy_posted.dat", substr($PendingTransaction, 1))) {
                      echo "<td width='30%'>" . $tradeInfo[2] . " BTC</td><td width='30%'>$" . $tradeInfo[3] . "</td><td width='30%'>$" . ($tradeInfo[2] * $tradeInfo[3]) . "</td>";
                      echo "<td><input type='button' value='Cancel Trade' onclick=\"document.getElementById('tcID').value='" . $tradeInfo[0] . "'; document.getElementById('tcTYPE').value='BUY'; document.getElementById('cancelForm').submit();\" /></td>\n";
                      echo "</tr>\n";
                    } else if($tradeInfo = GetTradeInfo($_SERVER['DOCUMENT_ROOT'] . "/data/buy.dat", substr($PendingTransaction, 1))) {
                      echo "<td width='30%'>" . $tradeInfo[2] . " BTC</td><td width='30%'>$" . $tradeInfo[3] . "</td><td width='30%'>$" . ($tradeInfo[2] * $tradeInfo[3]) . "</td>";
                      echo "<td><input type='button' value='Cancel Trade' onclick=\"document.getElementById('tcID').value='" . $tradeInfo[0] . "'; document.getElementById('tcTYPE').value='BUY'; document.getElementById('cancelForm').submit();\" /></td>\n";
                      echo "</tr>\n";
                    }
                  } else if("S" == $PendingTransaction[0]) {
                    echo "<tr style='background-color: #FFFFE0;'><td width='10%'>"; $index++;
                    echo "Sell</td>\n";
                    if($tradeInfo = GetTradeInfo($_SERVER['DOCUMENT_ROOT'] . "/data/sell_posted.dat", substr($PendingTransaction, 1))) {
                      echo "<td width='30%'>" . $tradeInfo[2] . " BTC</td><td width='30%'>$" . $tradeInfo[3] . "</td><td width='30%'>$" . ($tradeInfo[2] * $tradeInfo[3]) . "</td>";
                      echo "<td><input type='button' value='Cancel Trade' onclick=\"document.getElementById('tcID').value='" . $tradeInfo[0] . "'; document.getElementById('tcTYPE').value='SELL'; document.getElementById('cancelForm').submit();\" /></td>\n";
                      echo "</tr>\n";
                    } else if($tradeInfo = GetTradeInfo($_SERVER['DOCUMENT_ROOT'] . "/data/sell.dat", substr($PendingTransaction, 1))) {
                      echo "<td width='30%'>" . $tradeInfo[2] . " BTC</td><td width='30%'>$" . $tradeInfo[3] . "</td><td width='30%'>$" . ($tradeInfo[2] * $tradeInfo[3]) . "</td>";
                      echo "<td><input type='button' value='Cancel Trade' onclick=\"document.getElementById('tcID').value='" . $tradeInfo[0] . "'; document.getElementById('tcTYPE').value='SELL'; document.getElementById('cancelForm').submit();\" /></td></tr>\n";
                      echo "</tr>\n";
                    }
                  } else {
                    echo "No trades in system.</td>\n";
                  }
                }
                echo "</table><br />\n";
                // History data here...
                array_pop($history); // remove trailing line
                if("" != $history[0]){
                  echo "<table style='width: 100%; text-align: center;'><tr><td>Trade History</td></tr></table>\n";
                  echo "<table style='width: 100%'>\n";
                  foreach($history as $index => $value) {
                    $historyData = explode(",", $value);
                    if("S" == $historyData[0][0]) {
                      echo "<tr style='background-color: #FFFFC8'><td width='16%'>" . "Sale #" . $historyData[0] . "</td><td width='37%'>-" . $historyData[1] . " BTC</td><td width='37%'>&nbsp;$" . $historyData[2] . "</td></tr>";
                    } else if("B" == $historyData[0][0]) {
                      echo "<tr style='background-color: #C8FFFF'><td width='16%'>" . "Purchase #" . $historyData[0] . "</td><td width='37%'>&nbsp;" . $historyData[1] . " BTC</td><td width='37%'>-$" . $historyData[2] . "</td></tr>";
                    }
                  }
                  echo "</table>\n";
                }
                echo "</div><br />\n";
              }
            }
            
            // Put account modification tools here.
            echo "<hr>And now, magical update stuff!<br />\n";
            echo "        <a class=\"coinbase-button\" data-code=\"d2e11ab2150bc81911040ec4a6c9c632\" data-button-style=\"custom_large\" href=\"https://coinbase.com/checkouts/d2e11ab2150bc81911040ec4a6c9c632\">Pay With Bitcoin</a><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>"
            echo "<form action=\"/Account/update_account.php\" method=\"post\">\n";
            echo "  <input type='hidden' name='Return_URL' value='" . $_SERVER['REQUEST_URI'] . "'>\n";
            echo "  <input type='hidden' name='Balance' value='" . (($_SESSION['user']['Balance'] * $DIV_BY_AMOUNT) + (0.01 * $DIV_BY_AMOUNT)) . "'>\n";
            echo "Balance: " . ($_SESSION['user']['Balance']);
            echo "  <input type='submit' value='Add 0.01 BTC to Transferrable Balance'>\n";
            echo "</form>";
          }
        ?>     
      </div>
      <div id="sidebar">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/SIDEBAR.php"); ?>
      </div>
      <div id="footer">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/FOOTER.php"); ?>
      </div>
    </div>
  </body>
</html>
