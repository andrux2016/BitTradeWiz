<?php

// add_funds.php
//  Purpose: Store a user trade in the database when the user has purchased some amount of BTC.
//  When the user requests funds, post that request here.
//  Also, when admin fulfills a trade, remove said trade from the database.
//  The admin can also AJAX request a list of pending transactions, in that case, echo it back to him.

// Note:
//  There will be two separate databases, pending.dat and archive.dat for this.
//  pending.dat holds all of the pending requests that have not been fulfilled,
//  archive.dat holds ALL of the requests, pending or fulfilled, along with a status (pending, accepted, denied)
//  Archive.dat is used in case of system failure, and will be downloaded daily?

// Upon launch of site, this will be automated

$bContinue = true;
$errMessage = "";

// Add Funds
//  Get user trade ID, add it to database along with user ID.

// Withdraw Funds
//  Add user ID to database, along with amount requested

// Admin data request
//  Send all request information
//  Adds: UserID, TradeID, Amount, Date
//  Outs: UserID, UserBalance, Amount, Date

// Admin Update (Remove Trade)
//  Find the specified request and remove it from pending.dat, move it to archive.dat

if(!$bContinue) {
  echo $errMessage;
} else {
  // Redirect here
}

?>
