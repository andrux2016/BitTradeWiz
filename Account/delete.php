<?php session_start(); include_once($_SERVER['DOCUMENT_ROOT'] . "/sql_includes.php");

$errMessage = "";
$bContinue = true;

// So this file takes in an action from a form, and performs that action on the SQL database. Sanatize everything first.
$_POST['ID'] = escape_string($_POST['ID']);
if("delete_portfolio" == $_POST['action']) {
  if(!isset($connection)) {
    $connection = mysqli_connect($LOCALHOST, $USER, $SQL_PASSWORD, $DEFAULT_DB);
  }
  if($result = mysqli_query($connection, "DELETE FROM tbPracticeAccounts WHERE AcctID='" . $_POST['ID'] . "'")) {
    // Success.
  } else {
    $errMessage .= "Error deleting practice account #" . $_POST['ID'] . "<br />\n";
    $bContinue = false;
  }
  
  // Now, be sure to remove that from our list:
  $newList = "";
  array($newArray);
  foreach($_SESSION['user']['PracticeAcctIdList'] as $PracticeAcct) {
    if($PracticeAcct != $_POST['ID']) {
      $newList .= "$PracticeAcct,";
      array_push($newArray, $PracticeAcct);
    }
  }
  
  if($result = mysqli_query($connection, "UPDATE tbUserData SET PracticeAcctIdList='" . $newList . "' WHERE UserID='" . $_SESSION['user']['ID'] . "'")) {
    $_SESSION['user']['PracticeAcctIdList'] = $newArray;
    $errMessage .= "UPDATE tbUserData SET PracticeAcctIdList='" . $newList . "' WHERE UserID='" . $_SESSION['user']['ID'] . "'<br />\n";
    $bContinue = false;
  } else {
    $errMessage .= "Error updating PracticeAcctIdList for " . $_SESSION['user']['ID'] . "<br />\n";
    $bContinue = false;
  }
}

else if("delete_account" == $_POST['action']) {
  $connection = mysqli_connect($LOCALHOST, $USER, $SQL_PASSWORD, $DEFAULT_DB);
  
  // First, we have to delete all associated practice accounts!
  foreach($_SESSION['user']['PracticeAcctIdList'] as $PracticeAcct) {
    if($result = mysqli_query($connection, "DELETE FROM tbPracticeAccounts WHERE AcctID='" . $PracticeAcct . "'")) {
      // Success.
    } else {
      $errMessage .= "Error deleting practice account #" . $PracticeAcct . "<br />\n";
      $bContinue = false;
    }
  }
  
  if($result = mysqli_query($connection, "DELETE FROM tbUserData WHERE UserID='" . $_POST['ID'] . "'")) {
    unset($_SESSION['user']);
  } else {
    $errMessage .= ("SQL Error in deleting account " . $_POST['ID']);
    $bContinue = false;
  }
}

else {
  $errMessage .= "Unrecognized action!<br />\n";
  $bContinue = false;
}

if(true == $bContinue) {
  header("Location: " . $_POST['Return_URL']);
} else {
  echo $errMessage;
}

echo "Return URL: " . $_POST['Return_URL'];

?>
