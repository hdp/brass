// This JavaScript is absolutely abominable and needs to be extensively refactored
// but I'm not going to get around to doing that for some time
// If you are tempted to try to work with it I suggest doing something more rewarding instead!!!

function multishift(numpieces) {
    if ( thedetails.length < numpieces ) {
        throw('Array unexpectedly short when trying to retrieve event details');
    }
    var i;
    var rtnarray = [];
    for (i=0;i<numpieces;i++) {
        rtnarray[i] = Number(thedetails.shift());
    }
    return rtnarray;
}

function producetime(datetimebreak) {
    if ( newtime ) { thetime += newtime; }
    newtime = 0;
    var thedate = new Date(1000*thetime);
    var timestring = thedate.getUTCFullYear() + "-";
    var storage = thedate.getUTCMonth();
    storage++;
    if ( storage > 9 ) { timestring += storage + "-"; }
    else               { timestring += "0" + storage + "-"; }
    storage = thedate.getUTCDate();
    if ( storage > 9 ) { timestring += storage + datetimebreak; }
    else               { timestring += "0" + storage + datetimebreak; }
    storage = thedate.getUTCHours();
    if ( storage > 9 ) { timestring += storage + ":"; }
    else               { timestring += "0" + storage + ":"; }
    storage = thedate.getUTCMinutes();
    if ( storage > 9 ) { timestring += storage + ":"; }
    else               { timestring += "0" + storage + ":"; }
    storage = thedate.getUTCSeconds();
    if ( storage > 9 ) { timestring += storage + " GMT"; }
    else               { timestring += "0" + storage + " GMT"; }
    return timestring;
}

function tiledescription(tileid,activeplayer,sayown,sayflipped,sentencestart,saylocation) {
    var rtnstring;
    if ( activeplayer == spacestatus[tileid] ) {
        rtnstring = pronouns[activeplayer][3] + " ";
        if ( sayown ) { rtnstring += "own "; }
    } else if ( spacestatus[tileid] == 8 && sentencestart ) {
        rtnstring = "The orphan ";
    } else if ( spacestatus[tileid] == 8 ) {
        rtnstring = "the orphan ";
    } else {
        rtnstring = playerdetails[spacestatus[tileid]][1] + "'s ";
    }
    if ( sayflipped && spacecubes[tileid] ) { rtnstring += "unflipped "; }
    else if ( sayflipped )                  { rtnstring += "flipped "; }
    rtnstring += "Tech " + techlevels[tileid] + " " + industrynames[spacetile[tileid]];
    if ( saylocation ) { rtnstring += " on " + spacedescriptions[tileid]; }
    return rtnstring;
}

function fliptile(x) {
    if ( spacestatus[x] != 8 ) {
        incomespace[spacestatus[x]] += incomeboost[spacetile[x]][techlevels[x]-1];
        if ( incomespace[spacestatus[x]] > 99 ) { incomespace[spacestatus[x]] = 99; }
    }
    numflippedtiles[spacetowns[x]]++;
    spacecubes[x] = 0;
}

function destroytile(x) {
    if ( !spacecubes[x] ) { numflippedtiles[spacetowns[x]]--; }
    spacecubes[x] = 1;
    spacestatus[x] = 9;
    techlevels[x] = 0;
}

function endaction() {
    var i;
    if ( secondmove || ( round == 1 && !railphase ) ) {
        for (i=0;i<5;i++) {
            if ( turnorder[i] == currentplayer && i < 4 ) {
                currentplayer = turnorder[i+1];
                i = 10;
            }
        }
    }
    if ( round != 1 || railphase ) { secondmove = 1 - secondmove; }
    numactionstaken++;
    if ( numactionstaken == 2*numplayers ) { keepgoing = 1; }
}

function railscore() {
    var rtnstring = "Rail Phase scoring occurred at " + producetime(" ") + ". The players' scores beforehand are as follows:<ul>";
    var i;
    var j;
    var railpoints = [0,0,0,0,0];
    var tilepoints = [0,0,0,0,0];
    var moneypoints = [0,0,0,0,0];
    var moneytext = ["","","","",""];
    var windiscrim = [];
    var negativemoney;
    var pointstoadd;
    var pluralsstring;
    var mustknowmoney = 0;
    var mustknowturnorder = 0;
    var newturnordertext;
    var positionwidth = 100;
    var pointswidth = 90;
    var incomewidth = 135;
    var incometitle = "Income Space";
    var moneywrite = "";
    var turnorderwrite = "";
    var positionstext = ["1st","2nd","3rd","4th","5th"];
    var currentclass = 0;
    for (i=0;i<5;i++) {
        if ( playerexists[i] == 1 ) { rtnstring += "<li>" + playerdetails[i][1] + ": " + victorypoints[i]; }
    }
    rtnstring += "</ul>First of all, rail links are scored.<br><br><table width=600 cellpadding=3 align=center><thead class=\"colclass2\"><tr><td align=center width=265><b>Link</b></td><td align=center><b>Player</b></td><td align=center width=60><b>Points</b></td></tr></thead><tbody>";
    for (i=0;i<numraillinks;i++) {
        if ( linkstatus[i] < 8 ) {
            if ( playerexists[linkstatus[i]] == 1 ) {
                pointstoadd = numflippedtiles[linkstarts[i]] + numflippedtiles[linkends[i]];
                railpoints[linkstatus[i]] += pointstoadd;
                currentclass = 1 - currentclass;
                rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + locationnames[linkstarts[i]] + " — " + locationnames[linkends[i]] + "</td><td>" + playerdetails[linkstatus[i]][1] + "</td><td align=center>" + pointstoadd + "</td></tr>";
            }
        }
    }
    rtnstring += "</tbody></table><br>The number of points scored from rail links is as follows:<ul>";
    for (i=0;i<5;i++) {
        if ( playerexists[i] == 1 ) {
            if ( railpoints[i] == 1 ) { pluralsstring = ""; }
            else                      { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + railpoints[i] + " point" + pluralsstring;
        }
    }
    currentclass = 0;
    rtnstring += "</ul>Secondly, industry tiles are scored.<br><br><table width=600 cellpadding=3 align=center><thead class=\"colclass2\"><tr><td align=center width=165><b>Tile location</b></td><td align=center width=80><b>Tile type</b></td><td align=center width=45><b>Tech</b></td><td align=center><b>Player</b></td><td align=center width=50><b>Points</b></td></tr></thead><tbody>";
    for (i=0;i<numindustryspaces;i++) {
        if ( spacestatus[i] < 8 && !spacecubes[i] ) {
            if ( playerexists[spacestatus[i]] == 1 ) {
                pointstoadd = vpboost[spacetile[i]][techlevels[i]-1];
                tilepoints[spacestatus[i]] += pointstoadd;
                currentclass = 1 - currentclass;
                rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + briefspacedescriptions[i] + "</td><td align=center>" + industrynames[spacetile[i]] + "</td><td align=center>" + techlevels[i] + "</td><td>" + playerdetails[spacestatus[i]][1] + "</td><td align=center>" + pointstoadd + "</td></tr>";
            }
        }
    }
    rtnstring += "</tbody></table><br>The number of points scored from industry tiles is as follows:<ul>";
    currentclass = 0;
    for (i=0;i<5;i++) {
        if ( playerexists[i] == 1 ) {
            if ( tilepoints[i] == 1 ) { pluralsstring = ""; }
            else                      { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + tilepoints[i] + " point" + pluralsstring;
        }
    }
    rtnstring += "</ul>Finally, the players receive the following numbers of points for their remaining funds:<ul>";
    for (i=0;i<5;i++) {
        if ( playerexists[i] == 1 ) {
            moneypoints[i] = money[i];
            moneypoints[i] /= 10;
            moneypoints[i] = Math.floor(moneypoints[i]);
            if ( moneypoints[i] < 0 ) { moneypoints[i] = 0; }
            moneytext[i] = moneyformat(money[i]);
            if ( moneypoints[i] == 1 ) { pluralsstring = ""; }
            else                       { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + moneypoints[i] + " point" + pluralsstring + ", for having " + moneytext[i] + " remaining";
        }
    }
    for (i=0;i<5;i++) {
        if ( playerexists[i] == 1 ) {
            victorypoints[i] += railpoints[i] + tilepoints[i] + moneypoints[i];
            windiscrim.push([500000*victorypoints[i]+5000*incomespace[i],i]);
        }
    }
    if ( numplayers > 1 ) {
        mustknowmoney = 0;
        for (i=1;i<windiscrim.length;i++) {
            for (j=0;j<i;j++) {
                if ( windiscrim[j][0] == windiscrim[i][0] ) { mustknowmoney = 1; }
            }
        }
        if ( mustknowmoney ) {
            for (i=0;i<windiscrim.length;i++) { windiscrim[i][0] += 5*money[windiscrim[i][1]]; }
            mustknowturnorder = 0;
            for (i=1;i<windiscrim.length;i++) {
                for (j=0;j<i;j++) {
                    if ( windiscrim[j][0] == windiscrim[i][0] ) { mustknowturnorder = 1; }
                }
            }
            if ( mustknowturnorder ) {
                newturnordertext = doturnorder(0);
                for (i=0;i<windiscrim.length;i++) {
                    for (j=0;j<5;j++) {
                        if ( turnorder[j] == windiscrim[i][1] ) {
                            windiscrim[i][0] += j;
                            windiscrim[i][2] = j;
                        }
                    }
                }
            }
        }
    }
    windiscrim.sort(function(a,b){return b[0]-a[0]});
    rtnstring += "</ul>";
    if ( mustknowturnorder ) {
        rtnstring += "In order to determine player positions, it is necessary to calculate the turn order for the theoretical next turn. This turn order is: " + newturnordertext + ".<br><br>";
        positionwidth = 85;
        pointswidth = 75;
        incomewidth = 75;
        incometitle = "Income";
        moneywrite = "<td align=center width=75><b>Money</b></td>";
        turnorderwrite = "<td align=center width=110><b>Next turn</b></td>";
    } else if ( mustknowmoney ) {
        positionwidth = 100;
        pointswidth = 90;
        incomewidth = 135;
        moneywrite = "<td align=center width=75><b>Money</b></td>";
    }
    rtnstring += "Here are the results of the game:<br><br><table width=600 cellpadding=3 align=center><thead class=\"colclass2\"><tr><td align=center width=" + positionwidth + "><b>Position</b></td><td align=center><b>Player</b></td><td align=center width=" + pointswidth + "><b>Points</b></td><td align=center width=" + incomewidth + "><b>" + incometitle + "</b></td>" + moneywrite + turnorderwrite + "</tr></thead><tbody>";
    for (i=0;i<windiscrim.length;i++) {
        currentclass = 1 - currentclass;
        rtnstring += "<tr class=\"colclass" + currentclass + "\"><td align=right>" + positionstext[i] + "&nbsp;</td><td>&nbsp;" + playerdetails[windiscrim[i][1]][1] + "</td><td align=center>" + victorypoints[windiscrim[i][1]] + "</td><td align=center>" + incomespace[windiscrim[i][1]];
        if ( mustknowmoney ) { rtnstring += "</td><td align=center>" + moneytext[windiscrim[i][1]]; }
        if ( mustknowturnorder ) { rtnstring += "</td><td align=center>" + positionstext[windiscrim[i][2]]; }
        rtnstring += "</td></tr>";
    }
    rtnstring += "</tbody></table><br>Congratulations to " + playerdetails[windiscrim[0][1]][1] + "!";
    keepgoing = 0;
    numactionstaken = 0;
    return rtnstring;
}

function canalscore() {
    var rtnstring = "Canal Phase scoring occurred at " + producetime(" ") + ". First of all, canal links are scored.<br><br><table width=600 cellpadding=3 align=center><thead class=\"colclass2\"><tr><td align=center width=265><b>Link</b></td><td align=center><b>Player</b></td><td align=center width=60><b>Points</b></td></tr></thead><tbody>";
    var i;
    var canalpoints = [0,0,0,0,0];
    var tilepoints = [0,0,0,0,0];
    var pointstoadd;
    var pluralsstring;
    var removearea;
    var clearspacearray;
    var currentclass = 0;
    for (i=0;i<numcanallinks;i++) {
        if ( linkstatus[i] < 8 ) {
            if ( playerexists[linkstatus[i]] ) {
                pointstoadd = numflippedtiles[linkstarts[i]] + numflippedtiles[linkends[i]];
                canalpoints[linkstatus[i]] += pointstoadd;
                currentclass = 1 - currentclass;
                rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + locationnames[linkstarts[i]] + " — " + locationnames[linkends[i]] + "</td><td>" + playerdetails[linkstatus[i]][1] + "</td><td align=center>" + pointstoadd + "</td></tr>";
            }
        }
    }
    currentclass = 0;
    rtnstring += "</tbody></table><br>The number of points scored from canal links is as follows:<ul>";
    for (i=0;i<5;i++) {
        if ( playerexists[i] ) {
            if ( canalpoints[i] == 1 ) { pluralsstring = ""; }
            else                       { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + canalpoints[i] + " point" + pluralsstring;
        }
    }
    rtnstring += "</ul>Secondly, industry tiles are scored and/or removed.<br><br><table width=600 cellpadding=3 align=center><thead class=\"colclass2\"><tr><td align=center width=165><b>Tile location</b></td><td align=center width=80><b>Tile type</b></td><td align=center width=40><b>Tech</b></td><td align=center><b>Player</b></td><td align=center width=45><b>Points</b></td><td align=center width=35><b>Rmv</b></td></tr></thead><tbody>";
    for (i=0;i<numindustryspaces;i++) {
        if ( spacestatus[i] == 8 ) {
            currentclass = 1 - currentclass;
            rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + briefspacedescriptions[i] + "</td><td align=center>" + industrynames[spacetile[i]] + "</td><td align=center>" + techlevels[i] + "</td><td>";
            rtnstring += "Orphan</td><td></td><td align=center>Y</td></tr>";
            destroytile(i);
        } else {
            if ( !spacecubes[i] ) {
                currentclass = 1 - currentclass;
                rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + briefspacedescriptions[i] + "</td><td align=center>" + industrynames[spacetile[i]] + "</td><td align=center>" + techlevels[i] + "</td><td>";
                pointstoadd = vpboost[spacetile[i]][techlevels[i]-1];
                tilepoints[spacestatus[i]] += pointstoadd;
                rtnstring += playerdetails[spacestatus[i]][1] + "</td><td align=center>" + pointstoadd + "</td><td";
                if ( techlevels[i] == 1 ) {
                    rtnstring += " align=center>Y";
                    destroytile(i);
                } else {
                    rtnstring += ">";
                }
                rtnstring += "</td></tr>";
            } else if ( techlevels[i] == 1 ) {
                currentclass = 1 - currentclass;
                rtnstring += "<tr class=\"colclass" + currentclass + "\"><td>" + briefspacedescriptions[i] + "</td><td align=center>" + industrynames[spacetile[i]] + "</td><td align=center>" + techlevels[i] + "</td><td>";
                rtnstring += playerdetails[spacestatus[i]][1] + "</td><td></td><td align=center>Y</td></tr>";
                destroytile(i);
            }
        }
    }
    rtnstring += "</tbody></table><br>The number of points scored from industry tiles is as follows:<ul>";
    for (i=0;i<5;i++) {
        if ( playerexists[i] ) {
            if ( tilepoints[i] == 1 ) { pluralsstring = ""; }
            else                      { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + tilepoints[i] + " point" + pluralsstring;
        }
    }
    rtnstring += "</ul>The players' scores going into the Rail Phase are as follows:<ul>";
    for (i=0;i<5;i++) {
        victorypoints[i] = canalpoints[i] + tilepoints[i];
        if ( playerexists[i] ) {
            if ( victorypoints[i] == 1 ) { pluralsstring = ""; }
            else                         { pluralsstring = "s"; }
            rtnstring += "<li>" + playerdetails[i][1] + ": " + victorypoints[i] + " point" + pluralsstring;
        }
    }
    rtnstring += "</ul>";
    dmsalesmade = 0;
    cottondemand = 0;
    linkstarts = railstarts;
    linkends = railends;
    linkstatus = linkstatusrail;
    eventbits[3]++;
    eventbits[18]++;
    round = 0;
    railphase = 1;
    if ( numplayers == 4 ) { numrounds =  8; }
    else                   { numrounds = 10; }
    return rtnstring;
}

function doturnorder(dodebtcheck) {
    var rtnstring = "";
    var i;
    var j;
    var k;
    var tilestosell = [];
    var repayamount;
    var debtors = 0;
    var debtorscopy;
    var debtorsarray = [];
    var hasbeenforcedtosell = [0,0,0,0,0];
    var ihavenotiles = [1,1,1,1,1];
    var playersreceivingcash = [];
    var playerspayinginterest = [];
    var numplayersreceivingcash;
    var numplayerspayinginterest;
    var prcnames = "";
    var prcamounts = "";
    var ppinames = "";
    var ppiamounts = "";
    var firstitemlistedyet = 0;
    var moneysign;
    var absolutemoney;
    var sortingarray = [];
    var isdebtor;
    if ( dodebtcheck ) {
        while ( thedetails[0] != 96 ) {
            if ( thedetails.length == 0 ) { throw('Array unexpectedly short when looking for automatically sold tiles'); }
            tilestosell.push(Number(thedetails.shift()));
        }
        round++;
        rtnstring += "Start of round " + round + " of " + numrounds + ". ";
        for (i=0;i<5;i++) {
            if ( playerexists[i] ) {
                money[i] += incomeamounts[incomespace[i]];
                if ( incomespace[i] > 10 ) { playersreceivingcash.push(i); }
                if ( incomespace[i] < 10 ) { playerspayinginterest.push(i); }
            }
        }
        thedetails.shift();
        numplayersreceivingcash = playersreceivingcash.length;
        numplayerspayinginterest = playerspayinginterest.length;
        if ( numplayersreceivingcash == 1 ) {
            rtnstring += playerdetails[playersreceivingcash[0]][1] + " receives income of " + incomeamountstext[incomespace[playersreceivingcash[0]]] + ". ";
        } else if ( numplayersreceivingcash ) {
            for (i=0;i<numplayersreceivingcash;i++) {
                 if ( i == numplayersreceivingcash - 1 ) {
                     prcnames += " and ";
                     prcamounts += " and ";
                 } else if ( i ) {
                     prcnames += ", ";
                     prcamounts += ", ";
                 }
                 prcnames += playerdetails[playersreceivingcash[i]][1];
                 prcamounts += incomeamountstext[incomespace[playersreceivingcash[i]]];
            }
            rtnstring += prcnames + " receive income of " + prcamounts + ", respectively. ";
        }
        if ( numplayerspayinginterest == 1 ) {
            rtnstring += playerdetails[playerspayinginterest[0]][1] + " pays interest of " + incomeamountstext[incomespace[playerspayinginterest[0]]] + ". ";
        } else if ( numplayerspayinginterest ) {
            for (i=0;i<numplayerspayinginterest;i++) {
                 if ( i == numplayerspayinginterest - 1 ) {
                     ppinames += " and ";
                     ppiamounts += " and ";
                 } else if ( i ) {
                     ppinames += ", ";
                     ppiamounts += ", ";
                 }
                 ppinames += playerdetails[playerspayinginterest[i]][1];
                 ppiamounts += incomeamountstext[incomespace[playerspayinginterest[i]]];
            }
            rtnstring += ppinames + " pay interest of " + ppiamounts + ", respectively. ";
        }
        for (i=0;i<tilestosell.length;i++) {
            hasbeenforcedtosell[spacestatus[tilestosell[i]]] = 1;
            repayamount = costs[spacetile[tilestosell[i]]][techlevels[tilestosell[i]]-1];
            if ( repayamount % 2 ) { repayamount -= 1; }
            repayamount /= 2;
            money[spacestatus[tilestosell[i]]] += repayamount;
            rtnstring += "AUTOMATIC: " + playerdetails[spacestatus[tilestosell[i]]][1] + " sells " + pronouns[spacestatus[tilestosell[i]]][3] + " " + tiledescription(tilestosell[i],spacestatus[tilestosell[i]],0,1,0,1) + " for " + moneyformat(repayamount) + " to pay off " + pronouns[spacestatus[tilestosell[i]]][3] + " debt. ";
            destroytile(tilestosell[i]);
        }
        rtnstring += "The players' funds are currently as follows: ";
        for (i=0;i<5;i++) {
             if ( playerexists[i] ) {
                 if ( money[i] < 0 ) {
                     debtors++;
                     debtorsarray.push(i);
                     absolutemoney = -money[i];
                 } else {
                     absolutemoney = money[i];
                 }
                 if ( firstitemlistedyet ) { rtnstring += "; "; }
                 firstitemlistedyet = 1;
                 rtnstring += playerdetails[i][1] + ": " + moneyformat(absolutemoney);
             }
        }
        rtnstring += ". The new turn order is: ";
    }
    for (i=0;i<5;i++) {
        if ( !playerexists[i] ) { amountspent[i] += 300; }
        amountspent[i] *= 5;
        for (j=0;j<5;j++) {
            if ( turnorder[j] == i ) { amountspent[i] += j; }
        }
    }
    for (i=0;i<5;i++) { sortingarray[i] = [amountspent[i],i]; }
    sortingarray.sort(function(a,b){return a[0]-b[0]});
    for (i=0;i<5;i++) { turnorder[i] = sortingarray[i][1]; }
    for (i=0;i<numplayers;i++) {
        if ( i ) { rtnstring += ", "; }
        rtnstring += playerdetails[turnorder[i]][1];
    }
    rtnstring += ".";
    if ( dodebtcheck && debtors ) {
        for (i=0;i<numindustryspaces;i++) {
            if ( spacestatus[i] < 8 ) { ihavenotiles[spacestatus[i]] = 0; }
        }
        debtorscopy = debtors;
        for (i=0;i<debtorscopy;i++) {
            if ( ihavenotiles[debtorsarray[i]] ) {
                debtors--;
                debtorsarray.splice(i,1);
            }
        }
        if ( debtors == 1 && hasbeenforcedtosell[debtorsarray[0]] ) { rtnstring += " " + playerdetails[debtorsarray[0]][i] + " has more debt that " + pronouns[debtorsarray[0]][i] + " needs to pay off. This debt is to be paid off now."; }
        else if ( debtors == 1 ) { rtnstring += " " + playerdetails[debtorsarray[0]][i] + " has debt that " + pronouns[debtorsarray[0]][i] + " needs to pay off. This debt is to be paid off now."; }
        else if ( debtors > 1 ) { rtnstring += " " + debtors + " players have debt that they need to pay off. This debt is to be paid off now, in turn order."; }
        if ( debtors > 0 ) {
            debtmode = 1;
            for (i=0;i<5;i++) {
                for (j=0;j<5;j++) {
                    if ( turnorder[j] == i ) {
                        isdebtor = 0;
                        for (k=0;k<debtors;k++) {
                            if ( debtorsarray[k] == i ) { isdebtor = 1; }
                        }
                        if ( isdebtor ) {
                            currentplayer = i;
                            i = 9;
                        }
                    }
                }
            }
        } else {
            currentplayer = turnorder[0];
        }
    } else {
        currentplayer = turnorder[0];
    }
    amountspent = [0,0,0,0,0];
    numactionstaken = 0;
    keepgoing = 0;
    return rtnstring;
}

function orphanCMflip() {
    var rtnstring = "The orphan controller ";
    var ocmfbits = multishift(3);
    if ( ocmfbits[2] == 99 ) {
        if ( railphase ) { thedmtile = raildmtiles.pop(); }
        else             { thedmtile = canaldmtiles.pop(); }
        cottondemand += thedmtile;
        dmsalesmade++;
        if ( cottondemand < 8 ) {
            rtnstring += "sells cotton from " + tiledescription(ocmfbits[1],9,0,0,0,1) + " via the Distant Market. The tile drawn is -" + thedmtile + "; this leaves the demand marker on the " + dmdescrs[cottondemand] + " space.";
            fliptile(ocmfbits[1]);
        } else {
            rtnstring += "attempts to sell cotton from " + tiledescription(ocmfbits[1],9,0,0,0,1) + " via the Distant Market. But the tile drawn is -" + thedmtile + ", and so there's no more demand.";
        }
    } else {
        rtnstring += "sells cotton from " + tiledescription(ocmfbits[1],9,0,0,0,1) + " via " + tiledescription(ocmfbits[2],9,1,0,0,1) + ".";
        fliptile(ocmfbits[1]);
        fliptile(ocmfbits[2]);
    }
    return rtnstring;
}

function producelog() {
    var i;
    var j;
    var theoutput = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>Game log for game #" + gamenum + "</title><style type=\"text/css\">\n<!--\ntable {border-collapse: collapse}\ntd {border: 1px solid black}\n.colclass0 {background-color: #BFE7F4}\n.colclass1 {background-color: #DFDFDF}\n.colclass2 {background-color: #FFF3DC}\n-->\n</style></head><body>";
    var currentwrite = "";
    var intable = [1];
    var isturnorder = [1];
    var currentlyintable = 0;
    var currentclass = 0;
    var realclass;
    var movebyadmin;
    var numreplacements;
    var repayamount;
    var thetechlevel;
    var overbuildflag;
    var coalflag;
    var sellcubesflag;
    var numtosell;
    var thedmtile;
    var pluralsstring;
    var irregularevent = 0;
    var manualstart;
    var downsizeturnordercopy;
    var colournames = ["Red (","Yellow (","Green (","Purple (","Grey ("];
    var tolist = [];
    var temptime;
    var playertorepay;
    var someoneindebt;
    var debtorhastile;
    rawticker = rawticker.replace(/[A-J]/g,function(x){return ""+(x.charCodeAt(0)-65)+"|"});
    rawticker = rawticker.substring(0,rawticker.length-1);
    thedetails = rawticker.split("|");
    thedetailsnames = rawtickernames.split("|");
    manualstart = Number(thedetails.shift());
    thetime = Number(thedetails.shift());
    if ( !thetime ) { throw('Bad Start Time'); }
    outputarray[0] = "<tr><td>" + producetime("<br>") + "</td><td>Game started ";
    if ( manualstart ) {
        if ( thedetails.length < 2 || thedetailsnames.length == 0 ) {
            throw('Array unexpectedly short when looking for Starter details');
        }
        starterdetails[0] = Number(thedetails.shift());
        starterdetails[1] = thedetailsnames.shift();
        starterdetails[2] = Number(thedetails.shift());
        if ( starterdetails ) { outputarray[0] += "by " + starterdetails[1]; }
        else                  { throw('Bad Manual Start details'); }
    } else {
        outputarray[0] += "automatically, due to reaching its maximum number of players";
    }
    temptime = thetime;
    thetime = Number(thedetails.shift());
    if ( !thetime ) { throw('Bad Creation Time'); }
    if ( thedetails.length < 2 || thedetailsnames.length == 0 ) {
        throw('Array unexpectedly short when looking for Creator details');
    }
    creatordetails[0] = Number(thedetails.shift());
    creatordetails[1] = thedetailsnames.shift();
    creatordetails[2] = Number(thedetails.shift());
    outputarray[0] += ". (Game created by " + creatordetails[1] + " at " + producetime(" ") + ".) The initial turn order is: ";
    thetime = temptime;
    var playerexistencenum = Number(thedetails.shift());
    switch ( playerexistencenum ) {
        case  3: playerexists = [1,1,0,0,0]; break;
        case  5: playerexists = [1,0,1,0,0]; break;
        case  6: playerexists = [0,1,1,0,0]; break;
        case  7: playerexists = [1,1,1,0,0]; break;
        case  9: playerexists = [1,0,0,1,0]; break;
        case 10: playerexists = [0,1,0,1,0]; break;
        case 11: playerexists = [1,1,0,1,0]; break;
        case 12: playerexists = [0,0,1,1,0]; break;
        case 13: playerexists = [1,0,1,1,0]; break;
        case 14: playerexists = [0,1,1,1,0]; break;
        case 15: playerexists = [1,1,1,1,0]; break;
        case 17: playerexists = [1,0,0,0,1]; break;
        case 18: playerexists = [0,1,0,0,1]; break;
        case 19: playerexists = [1,1,0,0,1]; break;
        case 20: playerexists = [0,0,1,0,1]; break;
        case 21: playerexists = [1,0,1,0,1]; break;
        case 22: playerexists = [0,1,1,0,1]; break;
        case 23: playerexists = [1,1,1,0,1]; break;
        case 24: playerexists = [0,0,0,1,1]; break;
        case 25: playerexists = [1,0,0,1,1]; break;
        case 26: playerexists = [0,1,0,1,1]; break;
        case 27: playerexists = [1,1,0,1,1]; break;
        case 28: playerexists = [0,0,1,1,1]; break;
        case 29: playerexists = [1,0,1,1,1]; break;
        case 30: playerexists = [0,1,1,1,1]; break;
        default: throw('Unexpected player existence details');
    }
    turnorder = multishift(5);
    if ( !turnorder ) { throw('Array unexpectedly short when looking for initial Turn Order details'); }
    for (i=0;i<5;i++) {
        if ( playerexists[i] ) {
            numplayers++;
            if ( thedetails.length < 2 || thedetailsnames.length == 0 ) {
                throw('Array unexpectedly short when looking for player details');
            }
            playerdetails[i]    = [];
            playerdetails[i][0] = Number(thedetails.shift());
            playerdetails[i][1] = thedetailsnames.shift();
            playerdetails[i][2] = Number(thedetails.shift());
            playerdetails[i][3] = playerdetails[i][1];
            playerdetails[i][1] = colournames[i] + playerdetails[i][1] + ")";
            switch ( playerdetails[i][2] ) {
                case 0: pronouns[i] = ["He","he","His","his","Him","him"]; break;
                case 1: pronouns[i] = ["She","she","Her","her","Her","her"]; break;
                case 2: pronouns[i] = ["It","it","Its","its","It","it"]; break;
                default: throw('Bad value for player pronoun: ' + playerdetails[i][2] );
            }
        } else {
            playerdetails[i] = [0,"",0];
            pronouns[i] = ["","","","","",""];
        }
    }
    for (i=0;i<5;i++) {
        if ( playerexists[turnorder[i]] ) { tolist.push(playerdetails[turnorder[i]][1]); }
    }
    if ( numplayers > 2 ) {
        cubeprice = [1,1,2,2,3,3,4,4,5];
        money = [30,30,30,30,30];
        twoplayers = 0;
    } else {
        cubeprice = [2,2,3,3,4,4,5];
        money = [25,25,25,25,25];
        twoplayers = 1;
    }
    if ( numplayers == 4 ) { numrounds = 8; }
    outputarray[0] += tolist.join(", ") + ". The first player is " + tolist[0] + ". Let's go!</td></tr>";
    numactionstaken = numplayers;
    currentplayer = turnorder[0];
    while ( thedetails.length || keepgoing ) {
        if ( numactionstaken < 2 * numplayers ) {
            eventtype = Number(thedetails.shift());
            thebits = eventbits[eventtype];
            switch ( eventtype ) {
                case 7: case 22: thebits -= secondmove; break;
                case 97: thebits -= 2*secondmove; break;
                case 98: if ( !continuesellingmode ) { thebits++; } break;
            }
            bits = multishift(thebits);
            if ( eventtype == 31 ) {
                if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for comment text'); }
                commenttext = thedetailsnames.shift();
            }
            if ( eventtype != 56 && eventtype < 97 ) { newtime += bits[0]; }
            if ( eventtype > 15 && eventtype < 30 ) {
                movebyadmin = 1;
                if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for admin name for special move'); }
                usereventname = thedetailsnames.shift();
                if ( continuesellingmode || seconddevelopmode || secondrailmode ) {
                    if ( irregularevent ) {
                        irregularevent = 0;
                        arrayprogress++;
                        intable.push(1);
                        isturnorder.push(0);
                        outputarray[arrayprogress] = "";
                    }
                    outputarray[arrayprogress] += "<tr><td>" + producetime("<br>") + "</td><td>Under the control of " + usereventname + ", " + pronouns[currentplayer][1] + " then";
                } else if ( secondmove ) {
                    if ( irregularevent ) {
                        irregularevent = 0;
                        arrayprogress++;
                        intable.push(1);
                        isturnorder.push(0);
                        outputarray[arrayprogress] = "";
                    }
                    outputarray[arrayprogress] += "<tr><td>" + producetime("<br>") + "</td><td>Under the control of " + usereventname + ", for " + pronouns[currentplayer][3] + " second action, " + pronouns[currentplayer][1];
                } else {
                    irregularevent = 0;
                    arrayprogress++;
                    intable.push(1);
                    isturnorder.push(0);
                    outputarray[arrayprogress] = "<tr><td>" + producetime("<br>") + "</td><td>Under the control of " + usereventname + ", " + playerdetails[currentplayer][1];
                }
                bits.shift();
                bits.shift();
                eventtype -= 15;
            } else if ( eventtype < 15 && eventtype > 0 ) {
                movebyadmin = 0;
                if ( continuesellingmode || seconddevelopmode || secondrailmode ) {
                    if ( irregularevent ) {
                        irregularevent = 0;
                        arrayprogress++;
                        intable.push(1);
                        isturnorder.push(0);
                        outputarray[arrayprogress] = "";
                    }
                    outputarray[arrayprogress] += "<tr><td>" + producetime("<br>") + "</td><td>" + pronouns[currentplayer][0] + " then";
                } else if ( secondmove ) {
                    if ( irregularevent ) {
                        irregularevent = 0;
                        arrayprogress++;
                        intable.push(1);
                        isturnorder.push(0);
                        outputarray[arrayprogress] = "";
                    }
                    outputarray[arrayprogress] += "<tr><td>" + producetime("<br>") + "</td><td>For " + pronouns[currentplayer][3] + " second action, " + pronouns[currentplayer][1];
                } else {
                    irregularevent = 0;
                    arrayprogress++;
                    intable.push(1);
                    isturnorder.push(0);
                    outputarray[arrayprogress] = "<tr><td>" + producetime("<br>") + "</td><td>" + playerdetails[currentplayer][1];
                }
            } else if ( eventtype < 97 || ( eventtype == 97 && !secondmove ) ) {
                arrayprogress++;
                intable.push(1);
                isturnorder.push(0);
                outputarray[arrayprogress] = "<tr><td>" + producetime("<br>") + "</td><td>";
                irregularevent = 1;
            }
            switch ( eventtype ) {
                case 0:
                    repayamount = costs[spacetile[bits[1]]][techlevels[bits[1]]-1];
                    if ( repayamount % 2 ) { repayamount -= 1; }
                    repayamount /= 2;
                    playertorepay = spacestatus[bits[1]];
                    money[playertorepay] += repayamount;
                    outputarray[arrayprogress] += playerdetails[playertorepay][1] + " sells " + tiledescription(bits[1],playertorepay,0,1,0,1) + " to pay off " + pronouns[playertorepay][3] + " debt. " + pronouns[playertorepay][0] + " receives " + moneyformat(repayamount) + " and is left with " + moneyformat(money[playertorepay]) + ".</td></tr>";
                    destroytile(bits[1]);
                    someoneindebt = 9;
                    for (i=0;i<5;i++) {
                        if ( playerexists[turnorder[i]] && money[turnorder[i]] < 0 ) {
                            debtorhastile = 0;
                            for (j=0;j<numindustryspaces;j++) {
                                if ( spacestatus[j] == turnorder[i] ) {
                                    debtorhastile = 1;
                                }
                            }
                            if ( debtorhastile ) {
                                someoneindebt = turnorder[i];
                                i = 9;
                            }
                        }
                    }
                    if ( someoneindebt == 9 ) {
                        currentplayer = turnorder[0];
                        debtmode = 0;
                    } else {
                        currentplayer = someoneindebt;
                    }
                    break;
                case 1: case 2:
                    if ( eventtype == 1 ) {
                        outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]];
                        bits.unshift(0);
                    } else {
                        outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " and " + carddescriptions[bits[2]];
                        endaction();
                    }
                    remainingtiles[bits[3]][currentplayer]--;
                    thetechlevel = techleveldiscrim[bits[3]][remainingtiles[bits[3]][currentplayer]];
                    outputarray[arrayprogress] += " to build a Tech Level " + thetechlevel + " " + industrynames[bits[3]] + " on " + spacedescriptions[bits[4]];
                    if ( spacestatus[bits[4]] != 9 ) {
                        overbuildflag = 1;
                        outputarray[arrayprogress] += ", building over " + tiledescription(bits[4],currentplayer,1,1,0,0);
                        destroytile(bits[4]);
                    } else {
                        overbuildflag = 0;
                    }
                    money[currentplayer] -= costs[bits[3]][thetechlevel-1];
                    amountspent[currentplayer] += costs[bits[3]][thetechlevel-1];
                    sellcubesflag = 0;
                    switch ( bits[5] ) {
                        case 99:
                            coalflag = 1;
                            money[currentplayer] -= cubeprice[coaldemand];
                            amountspent[currentplayer] += cubeprice[coaldemand];
                            if ( overbuildflag ) { outputarray[arrayprogress] += " and "; }
                            else                 { outputarray[arrayprogress] += ", "; }
                            outputarray[arrayprogress] += "taking coal from the Demand Track for " + moneyformat(cubeprice[coaldemand]);
                            if ( coaldemand < 8 - 2*twoplayers ) { coaldemand++; }
                        break;
                        case 98:
                            coalflag = 0;
                        break;
                        case 97:
                            coalflag = 0;
                            sellcubesflag = 1;
                        break;
                        default:
                            coalflag = 1;
                            if ( overbuildflag ) { outputarray[arrayprogress] += " and "; }
                            else                 { outputarray[arrayprogress] += ", "; }
                            outputarray[arrayprogress] += "taking coal from " + tiledescription(bits[5],currentplayer,1,0,0,1);
                            if ( spacecubes[bits[5]] == 1 ) {
                                outputarray[arrayprogress] += " (which flips)";
                                fliptile(bits[5]);
                            } else {
                                spacecubes[bits[5]]--;
                            }
                        break;
                    }
                    switch ( bits[6] ) {
                        case 99:
                            money[currentplayer] -= cubeprice[irondemand];
                            amountspent[currentplayer] += cubeprice[irondemand];
                            if ( coalflag ) { outputarray[arrayprogress] += " and "; }
                            else if ( overbuildflag ) { outputarray[arrayprogress] += " and taking "; }
                            else { outputarray[arrayprogress] += ", taking "; }
                            outputarray[arrayprogress] += "iron from the Demand Track for " + moneyformat(cubeprice[irondemand]);
                            if ( irondemand < 8 - 2*twoplayers ) { irondemand++; }
                        break;
                        case 98:
                        break;
                        case 97:
                            sellcubesflag = 1;
                        break;
                        default:
                            if ( coalflag ) { outputarray[arrayprogress] += " and "; }
                            else if ( overbuildflag ) { outputarray[arrayprogress] += " and taking "; }
                            else { outputarray[arrayprogress] += ", taking "; }
                            outputarray[arrayprogress] += "iron from " + tiledescription(bits[6],currentplayer,1,0,0,1);
                            if ( spacecubes[bits[6]] == 1 ) {
                                outputarray[arrayprogress] += " (which flips)";
                                fliptile(bits[6]);
                            } else {
                                spacecubes[bits[6]]--;
                            }
                        break;
                    }
                    outputarray[arrayprogress] += ".";
                    spacetile[bits[4]] = bits[3];
                    spacestatus[bits[4]] = currentplayer;
                    techlevels[bits[4]] = thetechlevel;
                    spacecubes[bits[4]] = 1;
                    if ( bits[3] == 1 || bits[3] == 2 ) { spacecubes[bits[4]] = initialcubes[bits[3]-1][thetechlevel-1]; }
                    if ( bits[3] == 4 ) {
                        outputarray[arrayprogress] += " The newly constructed Shipyard immediately flips.";
                        fliptile(bits[4]);
                    } else if ( sellcubesflag && bits[3] == 1 ) {
                        numtosell = spacecubes[bits[4]];
                        if ( coaldemand < numtosell ) { numtosell = coaldemand; }
                        sellcubesflag = 0;
                        for (i=0;i<numtosell;i++) {
                            coaldemand--;
                            sellcubesflag += cubeprice[coaldemand];
                            spacecubes[bits[4]]--;
                        }
                        if ( numtosell == 1 ) { pluralsstring = ""; }
                        else                  { pluralsstring = "s"; }
                        outputarray[arrayprogress] += " The newly constructed Coal Mine sells " + numtosell + " coal cube" + pluralsstring + " to the Demand Track for " + moneyformat(sellcubesflag);
                        money[currentplayer] += sellcubesflag;
                        if ( spacecubes[bits[4]] ) {
                            outputarray[arrayprogress] += ".";
                        } else {
                            outputarray[arrayprogress] += ", and flips.";
                            fliptile(bits[4]);
                        }
                    } else if ( sellcubesflag ) {
                        numtosell = spacecubes[bits[4]];
                        if ( irondemand < numtosell ) { numtosell = irondemand; }
                        sellcubesflag = 0;
                        for (i=0;i<numtosell;i++) {
                            irondemand--;
                            sellcubesflag += cubeprice[irondemand];
                            spacecubes[bits[4]]--;
                        }
                        if ( numtosell == 1 ) { pluralsstring = ""; }
                        else                  { pluralsstring = "s"; }
                        outputarray[arrayprogress] += " The newly constructed Iron Works sells " + numtosell + " iron cube" + pluralsstring + " to the Demand Track for " + moneyformat(sellcubesflag);
                        money[currentplayer] += sellcubesflag;
                        if ( spacecubes[bits[4]] ) {
                            outputarray[arrayprogress] += ".";
                        } else {
                            outputarray[arrayprogress] += ", and flips.";
                            fliptile(bits[4]);
                        }
                    }
                    outputarray[arrayprogress] += "</td></tr>";
                    endaction();
                    break;
                case 3:
                    money[currentplayer] -= 3 + 2 * railphase;
                    amountspent[currentplayer] += 3 + 2 * railphase;
                    linkstatus[bits[2]] = currentplayer;
                    if ( railphase ) {
                        secondrailmode = 1;
                        outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to build the rail link between " + locationnames[linkstarts[bits[2]]] + " and " + locationnames[linkends[bits[2]]] + ", taking coal from ";
                        if ( bits[3] == 99 ) {
                            money[currentplayer] -= cubeprice[coaldemand];
                            amountspent[currentplayer] += cubeprice[coaldemand];
                            outputarray[arrayprogress] += "the Demand Track for " + moneyformat(cubeprice[coaldemand]);
                            if ( coaldemand < 8 - 2*twoplayers ) { coaldemand++; }
                        } else {
                            outputarray[arrayprogress] += tiledescription(bits[3],currentplayer,1,0,0,1);
                            if ( spacecubes[bits[3]] == 1 ) {
                                outputarray[arrayprogress] += " (which flips)";
                                fliptile(bits[3]);
                            } else {
                                spacecubes[bits[3]]--;
                            }
                        }
                        outputarray[arrayprogress] += ".</td></tr>";
                    } else {
                        outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to build the canal link between " + locationnames[linkstarts[bits[2]]] + " and " + locationnames[linkends[bits[2]]] + ".</td></tr>";
                        endaction();
                    }
                    break;
                case 4:
                    seconddevelopmode = 1;
                    remainingtiles[bits[2]][currentplayer]--;
                    outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to develop away a Tech Level " + techleveldiscrim[bits[2]][remainingtiles[bits[2]][currentplayer]] + " " + industrynames[bits[2]] + ", taking iron from ";
                    if ( bits[3] == 99 ) {
                        money[currentplayer] -= cubeprice[irondemand];
                        amountspent[currentplayer] += cubeprice[irondemand];
                        outputarray[arrayprogress] += "the Demand Track for " + moneyformat(cubeprice[irondemand]);
                        if ( irondemand < 8 - 2*twoplayers ) { irondemand++; }
                    } else {
                        outputarray[arrayprogress] += tiledescription(bits[3],currentplayer,1,0,0,1);
                        if ( spacecubes[bits[3]] == 1 ) {
                            outputarray[arrayprogress] += " (which flips)";
                            fliptile(bits[3]);
                        } else {
                            spacecubes[bits[3]]--;
                        }
                    }
                    outputarray[arrayprogress] += ".</td></tr>";
                    break;
                case 5:
                    loanamount = 10*bits[2];
                    money[currentplayer] += loanamount;
                    for (i=0;i<bits[2];i++) { incomespace[currentplayer] = incomefallback[incomespace[currentplayer]]; }
                    outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to take a loan of " + moneyformat(loanamount) + ", which puts " + pronouns[currentplayer][5] + " back to Income Space number " + incomespace[currentplayer] + " and leaves " + pronouns[currentplayer][5] + " with funds of " + moneyformat(money[currentplayer]) + ".";
                    endaction();
                    break;
                case 6:
                    continuesellingmode = 1;
                    outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to ";
                    if ( bits[3] == 99 ) {
                        if ( railphase ) { thedmtile = raildmtiles.pop(); }
                        else             { thedmtile = canaldmtiles.pop(); }
                        cottondemand += thedmtile;
                        dmsalesmade++;
                        if ( cottondemand < 8 ) {
                            outputarray[arrayprogress] += "sell cotton from " + tiledescription(bits[2],currentplayer,0,0,0,1) + " via the Distant Market. The tile drawn is -" + thedmtile + "; this leaves the demand marker on the " + dmdescrs[cottondemand] + " space.</td></tr>";
                            incomespace[currentplayer] += dmincome[cottondemand];
                            fliptile(bits[2]);
                        } else {
                            outputarray[arrayprogress] += "attempt to sell cotton from " + tiledescription(bits[2],currentplayer,0,0,0,1) + " via the Distant Market. But the tile drawn is -" + thedmtile + ", and so there's no more demand! Oh no!</td></tr>";
                            continuesellingmode = 0;
                            endaction();
                        }
                    } else {
                        outputarray[arrayprogress] += "sell cotton from " + tiledescription(bits[2],currentplayer,0,0,0,1) + " via " + tiledescription(bits[3],currentplayer,1,0,0,1) + ".</td></tr>";
                        fliptile(bits[2]);
                        fliptile(bits[3]);
                    }
                    break;
                case 7:
                    if ( !railphase && round == 1 ) { outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to pass on " + pronouns[currentplayer][3] + " first turn.</td></tr>"; }
                    else if ( secondmove ) { outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " to pass on " + pronouns[currentplayer][3] + " second action.</td></tr>"; }
                    else { outputarray[arrayprogress] += " uses " + carddescriptions[bits[1]] + " and " + carddescriptions[bits[2]] + " to pass on " + pronouns[currentplayer][3] + " entire turn.</td></tr>"; endaction(); }
                    endaction();
                    break;
                case 8:
                    if ( continuesellingmode ) {
                        if ( bits[2] == 99 ) {
                            if ( railphase ) { thedmtile = raildmtiles.pop(); }
                            else             { thedmtile = canaldmtiles.pop(); }
                            cottondemand += thedmtile;
                            dmsalesmade++;
                            if ( cottondemand < 8 ) {
                                outputarray[arrayprogress] += " sells cotton from " + tiledescription(bits[1],currentplayer,0,0,0,1) + " via the Distant Market. The tile drawn is -" + thedmtile + "; this leaves the demand marker on the " + dmdescrs[cottondemand] + " space.</td></tr>";
                                incomespace[currentplayer] += dmincome[cottondemand];
                                fliptile(bits[1]);
                            } else {
                                outputarray[arrayprogress] += " attempts to sell cotton from " + tiledescription(bits[1],currentplayer,0,0,0,1) + " via the Distant Market. But the tile drawn is -" + thedmtile + ", and so there's no more demand! Oh no!</td></tr>";
                                continuesellingmode = 0;
                                endaction();
                            }
                        } else {
                            outputarray[arrayprogress] += " sells cotton from " + tiledescription(bits[1],currentplayer,0,0,0,1) + " via " + tiledescription(bits[2],currentplayer,1,0,0,1) + ".</td></tr>";
                            fliptile(bits[1]);
                            fliptile(bits[2]);
                        }
                    } else if ( secondrailmode ) {
                        secondrailmode = 0;
                        money[currentplayer] -= 10;
                        amountspent[currentplayer] += 10;
                        linkstatus[bits[1]] = currentplayer;
                        outputarray[arrayprogress] += " builds the rail link between " + locationnames[linkstarts[bits[1]]] + " and " + locationnames[linkends[bits[1]]] + ", taking coal from ";
                        if ( bits[2] == 99 ) {
                            money[currentplayer] -= cubeprice[coaldemand];
                            amountspent[currentplayer] += cubeprice[coaldemand];
                            outputarray[arrayprogress] += "the Demand Track for " + moneyformat(cubeprice[coaldemand]);
                            if ( coaldemand < 8 - 2*twoplayers ) { coaldemand++; }
                        } else {
                            outputarray[arrayprogress] += tiledescription(bits[2],currentplayer,1,0,0,1);
                            if ( spacecubes[bits[2]] == 1 ) {
                                outputarray[arrayprogress] += " (which flips)";
                                fliptile(bits[2]);
                            } else {
                                spacecubes[bits[2]]--;
                            }
                        }
                        outputarray[arrayprogress] += ".</td></tr>";
                        endaction();
                    } else {
                        seconddevelopmode = 0;
                        remainingtiles[bits[1]][currentplayer]--;
                        outputarray[arrayprogress] += " develops away a Tech Level " + techleveldiscrim[bits[1]][remainingtiles[bits[1]][currentplayer]] + " " + industrynames[bits[1]] + ", taking iron from ";
                        if ( bits[2] == 99 ) {
                            money[currentplayer] -= cubeprice[irondemand];
                            amountspent[currentplayer] += cubeprice[irondemand];
                            outputarray[arrayprogress] += "the Demand Track for " + moneyformat(cubeprice[irondemand]);
                            if ( irondemand < 8 - 2*twoplayers ) { irondemand++; }
                        } else {
                            outputarray[arrayprogress] += tiledescription(bits[2],currentplayer,1,0,0,1);
                            if ( spacecubes[bits[2]] == 1 ) {
                                outputarray[arrayprogress] += " (which flips)";
                                fliptile(bits[2]);
                            } else {
                                spacecubes[bits[2]]--;
                            }
                        }
                        outputarray[arrayprogress] += ".</td></tr>";
                        endaction();
                    }
                    break;
                case 9:
                    if ( continuesellingmode ) {
                        continuesellingmode = 0;
                        outputarray[arrayprogress] += " decides to end " + pronouns[currentplayer][3] + " Sell action.</td></tr>";
                    } else if ( secondrailmode ) {
                        secondrailmode = 0;
                        outputarray[arrayprogress] += " decides not to build a second rail link.</td></tr>";
                    } else {
                        seconddevelopmode = 0;
                        outputarray[arrayprogress] += " decides not to develop a second tile.</td></tr>";
                    }
                    endaction();
                    break;
                case 30:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for admin name for clock alteration'); }
                    outputarray[arrayprogress] += thedetailsnames.shift() + " messed around with the game's clock.</td></tr>";
                    break;
                case 31:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for admin name for comment'); }
                    outputarray[arrayprogress] += thedetailsnames.shift() + " added the following comment: \"" + commenttext + "\"</td></tr>";
                    break;
                case 40: // This may appear in older games but cannot appear in newer ones
                    outputarray[arrayprogress] += playerdetails[currentplayer][1] + " quit the game. A replacement player will be required.</td></tr>";
                    break;
                case 41:
                case 42:
                    outputarray[arrayprogress] += playerdetails[bits[1]][1] + " quit the game.</td></tr>";
                    break;
                case 50:
                    outputarray[arrayprogress] += playerdetails[currentplayer][1] + " was kicked from the game automatically by the system.</td></tr>";
                    break;
                case 52:
                case 54:
                    outputarray[arrayprogress] += playerdetails[bits[4]][1] + " was kicked from the game by " + bits[2] + ".</td></tr>";
                    break;
                case 53:
                case 55:
                    outputarray[arrayprogress] += playerdetails[currentplayer][1] + " was kicked from the game by a unanimous vote amongst the other players.</td></tr>";
                    break;
                case 60:
                    outputarray[arrayprogress] += "The game was aborted by the system.</td></tr>";
                    break;
                case 61:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for admin name for game abort'); }
                    outputarray[arrayprogress] += "The game was aborted by " + thedetailsnames.shift() + ".</td></tr>";
                    break;
                case 62:
                    outputarray[arrayprogress] += "The game was aborted by a unanimous vote amongst the players.</td></tr>";
                    break;
                case 70:
                case 71:
                    if ( eventtype == 70 ) { outputarray[arrayprogress] += "The game was downsized automatically by the system."; }
                    else                   { outputarray[arrayprogress] += "The game was downsized by a unanimous vote amongst the players."; }
                    numreplacements = 0;
                    for (i=0;i<numindustryspaces;i++) {
                        if ( spacestatus[i] == currentplayer ) {
                            spacestatus[i] = 8;
                            numreplacements++;
                        }
                    }
                    if ( numreplacements == 1 ) { outputarray[arrayprogress] += " The industry tile left behind by " + playerdetails[currentplayer][1] + " has been converted to an orphan tile."; }
                    else if ( numreplacements ) { outputarray[arrayprogress] += " The " + numreplacements + " industry tiles left behind by " + playerdetails[currentplayer][1] + " have been converted to orphan tiles."; }
                    numreplacements = 0;
                    if ( railphase ) { j = numraillinks; }
                    else             { j = numcanallinks; }
                    for (i=0;i<j;i++) {
                        if ( linkstatus[i] == currentplayer ) {
                            linkstatus[i] = 8;
                            numreplacements++;
                        }
                    }
                    if ( numreplacements == 1 ) { outputarray[arrayprogress] += " The " + phasenames[railphase] + " link left behind by " + playerdetails[currentplayer][1] + " has been converted to an orphan link."; }
                    else if ( numreplacements ) { outputarray[arrayprogress] += " The " + numreplacements + " " + phasenames[railphase] + " links left behind by " + playerdetails[currentplayer][1] + " have been converted to orphan links."; }
                    switch ( numplayers ) {
                        case 4:
                            if ( railphase ) {
                                if ( round < 3 ) {
                                    outputarray[arrayprogress] += " There will now be 10 rounds in the Rail Phase, instead of 8.";
                                    numrounds = 10;
                                } else if ( round == 3 ) {
                                    outputarray[arrayprogress] += " There will now be 9 rounds in the Rail Phase, instead of 8.";
                                    numrounds = 9;
                                } else {
                                    outputarray[arrayprogress] += " The number of rounds in the Rail Phase does not change, as it is too late in the Phase.";
                                }
                            } else {
                                if ( round < 3 ) {
                                    outputarray[arrayprogress] += " There will now be 10 rounds in the Canal Phase, instead of 8. There will also be 10 rounds in the Rail Phase.";
                                    numrounds = 10;
                                } else if ( round == 3 ) {
                                    outputarray[arrayprogress] += " There will now be 9 rounds in the Canal Phase, instead of 8. There will be 10 rounds in the Rail Phase.";
                                    numrounds = 9;
                                } else {
                                    outputarray[arrayprogress] += " The number of rounds in the Canal Phase does not change, as it is too late in the Phase. However, there will be 10 rounds in the Rail Phase.";
                                }
                            }
                            break;
                        case 3:
                            throw('Unexpected downsize (three players)');
                            break;
                        case 2:
                            var theremainingplayer;
                            for (i=0;i<5;i++) {
                                if ( playerexists[i] && currentplayer != i ) {
                                    theremainingplayer = i;
                                }
                            }
                            outputarray[arrayprogress] += " This leaves only " + playerdetails[theremainingplayer][1] + " in the game, so " + playerdetails[theremainingplayer][3] + " wins the game by default.";
                            if ( railphase && ( numrounds == round || numrounds == round + 1 ) ) {
                                outputarray[arrayprogress] += " Since the game was almost over, scoring will take place in order to help us see what " + playerdetails[theremainingplayer][3] + "'s score would have been.</td></tr>";
                                arrayprogress++;
                                intable.push(0);
                                isturnorder.push(0);
                                outputarray[arrayprogress] = railscore();
                            }
                    }
                    outputarray[arrayprogress] +=  "</td></tr>";
                    downsizeturnordercopy = [];
                    for (i=0;i<5;i++) {
                        if ( turnorder[i] != currentplayer ) {
                            downsizeturnordercopy.push(turnorder[i]);
                        }
                    }
                    downsizeturnordercopy[4] = currentplayer;
                    playerexists[currentplayer] = 0;
                    if ( turnorder[4] != currentplayer ) {
                        for (i=0;i<4;i++) {
                            if ( turnorder[i] == currentplayer ) {
                                currentplayer = turnorder[i+1];
                                i = 10;
                            }
                        }
                    }
                    turnorder = downsizeturnordercopy.slice(0);
                    if ( secondmove || ( round == 1 && !railphase ) ) {
                        secondmove = 0;
                        numactionstaken--;
                    }
                    numplayers--;
                    if ( numplayers > 1 && debtmode ) {
                        someoneindebt = 9;
                        for (i=0;i<5;i++) {
                            if ( playerexists[turnorder[i]] && money[turnorder[i]] < 0 ) {
                                debtorhastile = 0;
                                for (j=0;j<numindustryspaces;j++) {
                                    if ( spacestatus[j] == turnorder[i] ) {
                                        debtorhastile = 1;
                                    }
                                }
                                if ( debtorhastile ) {
                                    someoneindebt = turnorder[i];
                                    i = 9;
                                }
                            }
                        }
                        if ( someoneindebt == 9 ) {
                            currentplayer = turnorder[0];
                            debtmode = 0;
                        } else {
                            currentplayer = someoneindebt;
                        }
                    }
                    break;
                case 80:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for replacement name'); }
                    usereventname = thedetailsnames.shift();
                    outputarray[arrayprogress] += usereventname + " offered to join the game as a replacement.</td></tr>";
                    playerdetails[currentplayer] = [bits[1],usereventname,bits[2],usereventname];
                    playerdetails[currentplayer][1] = colournames[currentplayer] + playerdetails[currentplayer][1] + ")";
                    switch ( bits[2] ) {
                        case 0: pronouns[currentplayer] = ["He","he","His","his","Him","him"]; break;
                        case 1: pronouns[currentplayer] = ["She","she","Her","her","Her","her"]; break;
                        case 2: pronouns[currentplayer] = ["It","it","Its","its","It","it"]; break;
                        default: throw('Bad value for replacement pronoun');
                    }
                    break;
                case 81: // This may appear in older games, but in newer ones it is superceded by 84
                    outputarray[arrayprogress] += playerdetails[bits[1]][1] + " accepted " + playerdetails[currentplayer][1] + "'s offer to join as a replacement.</td></tr>";
                    break;
                case 82: // This may appear in older games, but cannot appear in newer ones
                    outputarray[arrayprogress] += playerdetails[bits[1]][1] + " rejected " + playerdetails[currentplayer][1] + "'s offer to join as a replacement.</td></tr>";
                    break;
                case 83: // This may appear in older games, but in newer ones it is superceded by 85
                    outputarray[arrayprogress] += playerdetails[currentplayer][1] + " withdrew " + pronouns[currentplayer][3] + " offer to join as a replacement.</td></tr>";
                    break;
                case 84:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for accepted replacement name'); }
                    usereventname = thedetailsnames.shift();
                    playerdetails[currentplayer] = [bits[1],usereventname,bits[2],usereventname];
                    playerdetails[currentplayer][1] = colournames[currentplayer] + playerdetails[currentplayer][1] + ")";
                    switch ( bits[2] ) {
                        case 0: pronouns[currentplayer] = ["He","he","His","his","Him","him"]; break;
                        case 1: pronouns[currentplayer] = ["She","she","Her","her","Her","her"]; break;
                        case 2: pronouns[currentplayer] = ["It","it","Its","its","It","it"]; break;
                        default: throw('Bad value for accepted replacement pronoun');
                    }
                    outputarray[arrayprogress] += playerdetails[bits[3]][1] + " accepted " + playerdetails[currentplayer][1] + "'s offer to join as a replacement.</td></tr>";
                    break;
                case 85:
                    if ( thedetailsnames.length == 0 ) { throw('Array unexpectedly short when looking for withdrawing replacement name'); }
                    usereventname = thedetailsnames.shift();
                    playerdetails[currentplayer] = [bits[1],usereventname,bits[2],usereventname];
                    playerdetails[currentplayer][1] = colournames[currentplayer] + playerdetails[currentplayer][1] + ")";
                    switch ( bits[2] ) {
                        case 0: pronouns[currentplayer] = ["He","he","His","his","Him","him"]; break;
                        case 1: pronouns[currentplayer] = ["She","she","Her","her","Her","her"]; break;
                        case 2: pronouns[currentplayer] = ["It","it","Its","its","It","it"]; break;
                        default: throw('Bad value for withdrawing replacement pronoun');
                    }
                    outputarray[arrayprogress] += playerdetails[currentplayer][3] + " withdrew " + pronouns[currentplayer][3] + " offer to join as a replacement.</td></tr>";
                    break;
                case 91:
                    outputarray[arrayprogress] += playerdetails[bits[1]][1] + " decided to quit the game; this will happen when it is next " + pronouns[bits[1]][5] + " to move.</td></tr>";
                    break;
                case 97:
                    if ( secondmove ) {
                        outputarray[arrayprogress] += "<tr><td></td><td>Automatic: " + playerdetails[currentplayer][1] + " has nothing " + pronouns[currentplayer][1] + " can do for " + pronouns[currentplayer][3] + " second action, so " + pronouns[currentplayer][1] + " discards " + carddescriptions[bits[0]] + " and passes.</td></tr>";
                    } else {
                        outputarray[arrayprogress] += "Automatic: " + playerdetails[currentplayer][1] + " has nothing " + pronouns[currentplayer][1] + " can do on " + pronouns[currentplayer][3] + " last turn, so " + pronouns[currentplayer][1] + " discards " + carddescriptions[bits[0]] + " and " + carddescriptions[bits[1]] + " and passes.</td></tr>";
                        if ( bits[2] == 2 ) {
                            outputarray[arrayprogress] += pronouns[currentplayer][0] + " is then kicked from the game by an administrator.";
                            playerexists[currentplayer] = 5;
                            numplayers--;
                        } else if ( bits[2] ) {
                            outputarray[arrayprogress] += pronouns[currentplayer][0] + " then quits the game.";
                            playerexists[currentplayer] = 5;
                            numplayers--;
                        }
                        endaction();
                    }
                    endaction();
                    break;
                case 98:
                    if ( continuesellingmode ) {
                        continuesellingmode = 0;
                        outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is then obliged to end " + pronouns[currentplayer][3] + " Sell action.</td></tr>";
                    } else if ( seconddevelopmode ) {
                        seconddevelopmode = 0;
                        if ( bits[0] ) { outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is unable to develop away a second tile with this action, as " + pronouns[currentplayer][1] + " has run out of industry tiles to develop away.</td></tr>"; }
                        else           { outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is unable to develop away a second tile with this action, as " + pronouns[currentplayer][1] + " cannot afford to buy iron from the Demand Track.</td></tr>"; }
                    } else {
                        secondrailmode = 0;
                        switch ( bits[0] ) {
                            case 1: outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is unable to build a second rail link with this action, as " + pronouns[currentplayer][1] + " has run out of rail markers.</td></tr>"; break;
                            case 2: outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is unable to build a second rail link with this action, as " + pronouns[currentplayer][1] + " has insufficient funds.</td></tr>"; break;
                            default: outputarray[arrayprogress] += "<tr><td></td><td>" + pronouns[currentplayer][0] + " is unable to build a second rail link with this action.</td></tr>";
                        }
                    }
                    endaction();
                    break;
                case 99:
                    outputarray[arrayprogress] += "<tr><td></td><td>It is now impossible for the next Distant Market tile drawn to not zero the Cotton Demand marker, so the marker has been automatically moved to the \"No More Demand\" space by the system.</td></tr>";
                    break;
                default: throw('Unexpected event type');
            }
        } else if ( round == numrounds ) {
            while ( thedetails.length &&
                    ( thedetails[0] == 75 ||
                      thedetails[0] == 99
                      )
                    ) {
                arrayprogress++;
                intable.push(1);
                isturnorder.push(1);
                if ( thedetails[0] == 75 ) {
                    outputarray[arrayprogress] = "<tr><td></td><td>" + orphanCMflip() + "</td></tr>";
                } else {
                    outputarray[arrayprogress] += "<tr><td></td><td>It is now impossible for the next Distant Market tile drawn to not zero the Cotton Demand marker, so the marker has been automatically moved to the \"No More Demand\" space by the system.</td></tr>";
                    thedetails.shift();
                }
            }
            irregularevent = 0;
            arrayprogress++;
            intable.push(0);
            isturnorder.push(0);
            if ( railphase ) {
                outputarray[arrayprogress] = railscore();
            } else {
                outputarray[arrayprogress] = canalscore();
            }
        } else {
            if ( thedetails.length && thedetails[0] == 99 ) {
                arrayprogress++;
                intable.push(1);
                isturnorder.push(1);
                outputarray[arrayprogress] += "<tr><td></td><td>It is now impossible for the next Distant Market tile drawn to not zero the Cotton Demand marker, so the marker has been automatically moved to the \"No More Demand\" space by the system.</td></tr>";
                thedetails.shift();
            }
            arrayprogress++;
            intable.push(1);
            isturnorder.push(1);
            outputarray[arrayprogress] = "<tr><td></td><td>" + doturnorder(1) + "</td></tr>";
        }
    }
    if ( reverseticker ) {
        outputarray.reverse();
        intable.reverse();
    }
    for (i=0;i<outputarray.length;i++) {
        if ( intable[i] && currentlyintable ) {
            if ( isturnorder[i] ) {
                realclass = 2;
            } else {
                currentclass = 1 - currentclass;
                realclass = currentclass;
            }
            theoutput += "<tbody class=\"colclass" + realclass + "\">" + outputarray[i] + "</tbody>";
        } else if ( intable[i] ) {
            currentlyintable = 1;
            if ( isturnorder[i] ) { realclass = 2; }
            else                  { realclass = 0; }
            theoutput += "<table cellpadding=4 cellspacing=0><colgroup width=115></colgroup><colgroup></colgroup><tbody class=\"colclass" + realclass + "\">" + outputarray[i] + "</tbody>";
        } else if ( currentlyintable ) {
            currentlyintable = 0;
            currentclass = 0;
            theoutput += "</table><p>" + outputarray[i] + "</p>";
        } else {
            theoutput += "<p>" + outputarray[i] + "</p>";
        }
    }
    if ( currentlyintable ) { theoutput += "</table>"; }
    theoutput += "</body></html>";
    document.write(theoutput);
}