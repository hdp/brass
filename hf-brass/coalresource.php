<?php

function ClosestCoal($destinationa,$destinationb,$specifiedsource) {
    // Uses Dijkstra's algorithm for graphs with unit edge weights to determine whether the
    // source of coal for building is valid. If building a rail link, then $destinationa and
    // $destinationb should be the locations at the start and end of that link. If building
    // an industry tile, then $destinationa should be the industry space - *not* the
    // location - and $destinationb should be integer 50. If attempting to take coal from a
    // Coal Mine, then $specifiedsource should be the Coal Mine in question; otherwise it
    // should be integer 50 (actually any integer at least as large as NumIndustrySpaces
    // will do, IIRC). If the source is valid, then function returns the source
    // $specifiedsource. Otherwise it returns an appropriate integer error code, to be
    // intercepted by another function.
    global $GAME;
    if ( $specifiedsource < $GAME['NumIndustrySpaces'] and
         ( $GAME['SpaceStatus'][$specifiedsource] == 9 or
           $GAME['SpaceTile'][$specifiedsource] != 1 or
           !$GAME['SpaceCubes'][$specifiedsource]
           )
         ) {
        return 90;
            // The selected source of coal is not valid
            // (it's unoccupied, not a Coal Mine, or already flipped)
    }
    if ( $GAME['RailPhase'] ) {
        $NumLinks           = $GAME['NumRailLinks'];
        $LinkStarts         = $GAME['RailStarts'];
        $LinkEnds           = $GAME['RailEnds'];
        $LinkExistenceArray = $GAME['RailExistenceArray'];
        $LinkAlwaysExists   = $GAME['RailAlwaysExists'];
    } else {
        $NumLinks           = $GAME['NumCanalLinks'];
        $LinkStarts         = $GAME['CanalStarts'];
        $LinkEnds           = $GAME['CanalEnds'];
        $LinkExistenceArray = $GAME['CanalExistenceArray'];
        $LinkAlwaysExists   = $GAME['CanalAlwaysExists'];
    }
    $numtobevisited = $GAME['NumTowns'] - 1;
        // The number of locations that we will visit
        // (one location starts off already visited).
    for ($i=0;$i<$GAME['NumTowns'];$i++) {
        $CoalAvailable[$i] = false;
        $visited[$i] = false;
        $distance[$i] = 1000;
            // Initially, as far as we know there is no coal anywhere,
            // and we have not visited any locations.
            // 1000 is a surrogate for infinity.
    }
    if ( $destinationb == 50 ) {
        $destinationa = $GAME['spacetowns'][$destinationa];
        $currentlocation = $destinationa;
            // Watch out - I do need to change the value of $destinationa,
            // because it is used below.
        for ($i=0;$i<$GAME['NumTowns'];$i++) {
            if ( $GAME['CoalNet'][$i] == $GAME['CoalNet'][$currentlocation] ) {
                $tobevisited[$i] = true;
                    // I will visit this location, because it is in
                    // the same connected component of the coal network.
            } else {
                $tobevisited[$i] = false;
                $numtobevisited--;
                    // I will not be visiting this location, because it is not in
                    // the same connected component of the coal network.
            }
        }
        $distance[$currentlocation] = 0;
    } else {
        $currentlocation = $destinationa;
        for ($i=0;$i<$GAME['NumTowns'];$i++) {
            if ( $GAME['CoalNet'][$i] == $GAME['CoalNet'][$destinationa] or
                 $GAME['CoalNet'][$i] == $GAME['CoalNet'][$destinationb]
                 ) {
                $tobevisited[$i] = true;
                    // I will visit this location, because it is in one of the (up to) two
                    // connected components of the coal network that we are interested in.
            } else {
                $tobevisited[$i] = false;
                $numtobevisited--;
                    // I will not visit this location, because it is not in
                    // either of those (up to) two connected components.
            }
        }
        $distance[$destinationa] = 0;
        $distance[$destinationb] = 0;
    }
    $visited[$currentlocation] = true;
        // I have visited my start location.
    $RealSourceExists = false;
    for ($i=0;$i<$GAME['NumIndustrySpaces'];$i++) {
        if ( $GAME['SpaceStatus'][$i] != 9 and
             $GAME['SpaceCubes'][$i] and
             $GAME['SpaceTile'][$i] == 1 and
             $tobevisited[$GAME['spacetowns'][$i]]
             ) {
            $CoalAvailable[$GAME['spacetowns'][$i]] = true;
            $RealSourceExists = true;
                // I found a Coal Mine that's in a location I'll be visiting.
        }
    }
    if ( $specifiedsource < $GAME['NumIndustrySpaces'] and
         !$tobevisited[$GAME['spacetowns'][$specifiedsource]]
         ) {
        return 93;
            // The selected source of coal is not connected to the location
            // where you are trying to build.
    }
    if ( $specifiedsource < $GAME['NumIndustrySpaces'] ) {
        while ( $numtobevisited ) {
            // Resort to using Dijkstra's algorithm at this point.
            // If I were really trying hard to be efficient then I could
            // have first tried to determine whether there is actually more than
            // one location (that I will visit) that has an unflipped Coal Mine,
            // but I'm not and I won't.
            for ($i=0;$i<$NumLinks;$i++) {
                if ( ( $LinkAlwaysExists[$i] or
                       ( $GAME['ModularBoardParts'] & $LinkExistenceArray[$i] ) == $LinkExistenceArray[$i]
                       ) and
                     $GAME['LinkStatus'][$i] != 9
                     ) {
                    if ( $LinkStarts[$i] == $currentlocation and
                         $distance[$LinkEnds[$i]] > $distance[$currentlocation] + 1
                         ) {
                        $distance[$LinkEnds[$i]] = $distance[$currentlocation] + 1;
                    }
                    if ( $LinkEnds[$i] == $currentlocation and
                         $distance[$LinkStarts[$i]] > $distance[$currentlocation] + 1
                         ) {
                        $distance[$LinkStarts[$i]] = $distance[$currentlocation] + 1;
                    }
                }
            }
            $smallestdistance = 999;
            for ($i=0;$i<$GAME['NumTowns'];$i++) {
                if ( !$visited[$i] and $distance[$i] < $smallestdistance ) {
                    $currentlocation = $i;
                    $smallestdistance = $distance[$i];
                }
            }
            $visited[$currentlocation] = true;
            $numtobevisited--;
        }
        $smallestdistance = 999;
        for ($i=0;$i<$GAME['NumTowns'];$i++) {
            if ( $distance[$i] < $smallestdistance and $CoalAvailable[$i] ) {
                $smallestdistance = $distance[$i];
                    // I want to know the smallest distance to a place
                    // with an unflipped Coal Mine.
            }
        }
        if ( $distance[$GAME['spacetowns'][$specifiedsource]] == $smallestdistance ) {
            return $specifiedsource;
                // The source of coal you chose was valid.
        } else {
            return 92;
                // The selected source of coal is not the nearest.
        }
    } else if ( $RealSourceExists ) {
        return 91;
            // You cannot buy coal from the Demand Track,
            // as there is coal available on the board for you to use.
    } else if ( $GAME['HasPort'][$destinationa] or
                ( $destinationb != 50 and
                  $GAME['HasPort'][$destinationb]
                  )
                ) {
        return 50;
            // You are cleared to buy coal from the Demand Track.
    } else {
        return 95;
            // You cannot buy coal from the Demand Track,
            // as the location where you want to build is not connected to a Port.
    }
}

?>