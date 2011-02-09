<?php

// I had a go at improving how this works a while ago, but I pretty much
// screwed up the implementation of it. This script needs refactoring so that
// it does things in less hackish ways.

define( 'MAX_CODE_LINE_LENGTH'   , 110 );
define( 'MAX_GAME_STATUS_CHECKS' ,  75 );

define( 'GAMELINK_DOES_NOT_EXIST' , 0 );
define( 'GAMELINK_LOBBY'          , 1 );
define( 'GAMELINK_BOARD'          , 2 );
define( 'BLOCK_PARAGRAPH'         , 0 );
define( 'BLOCK_CODE_BLOCK'        , 1 );

function rsemitrim ($x) {
    if ( !strlen($x) ) { return ''; }
    $whitespace_chars = array("\r", "\t", ' ');
    if ( in_array($x[strlen($x)-1], $whitespace_chars) ) {
        return rtrim($x).' ';
    }
    return $x;
}

function msg_serialise ($msg) {
    $running_length = 0;
    $text = '';
    $block_types = array();
    $block_offsets = array();
    if ( !count($msg[0]) ) { return ''; }
    for ($i=0; $i<count($msg[0]); $i++) {
        $text .= $msg[0][$i];
        $block_types[] = $msg[1][$i];
        $block_offsets[] = $running_length;
        $running_length += $msg[2][$i];
    }
    return array( implode('', $block_types).';'.
                      implode(',', $block_offsets).':',
                  $text
                  );
}

function tilepic_standardise ($spec) {
    $chararray = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
                        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
                        '0', '1', '2', '3', '4', '5', '6', '!'
                        );
    $spec = strtolower($spec);
    $spec = str_replace('1 cube', 'c1', $spec);
    $spec = str_replace('cube 1', 'c1', $spec);
    $spec = preg_replace('/([0-6]) cubes/', 'c$1', $spec);
    $spec = preg_replace('/cubes ([0-6])/', 'c$1', $spec);
    for ($i=0; $i<strlen($spec); $i++) {
        if ( !in_array($spec[$i], $chararray) ) {
            $spec[$i] = '!';
        }
    }
    $spec = explode('!', $spec);
    $colour    = 7;
    $tiletype  = 0;
    $techlevel = 1;
    $flipped   = 0;
    $numcubes  = 0;
    for ($i=0; $i<count($spec); $i++) {
        switch ( $spec[$i] ) {
            case '0':  $techlevel = 0; break;
            case '1':  $techlevel = 1; break;
            case '2':  $techlevel = 2; break;
            case '3':  $techlevel = 3; break;
            case '4':  $techlevel = 4; break;
            case 'c0': $numcubes  = 0; break;
            case 'c1': $numcubes  = 1; break;
            case 'c2': $numcubes  = 2; break;
            case 'c3': $numcubes  = 3; break;
            case 'c4': $numcubes  = 4; break;
            case 'c5': $numcubes  = 5; break;
            case 'c6': $numcubes  = 6; break;
            case 'cotton':
            case 'cottonmill':
            case 'mill':
                $tiletype = 0;
                break;
            case 'coal':
            case 'coalmine':
            case 'mine':
                $tiletype = 1;
                break;
            case 'iron':
            case 'ironworks':
            case 'works':
                $tiletype = 2;
                break;
            case 'port':
                $tiletype = 3;
                break;
            case 'ship':
            case 'shipyard':
            case 'sy':
            case 'yard':
                $tiletype = 4;
                break;
            case 'red':
            case 'rot':
            case 'rouge':
                $colour = 0;
                break;
            case 'gelb':
            case 'gold':
            case 'jaune':
            case 'yellow':
                $colour = 1;
                break;
            case 'green':
            case 'gruen':
            case 'grun':
            case 'vert':
                $colour = 2;
                break;
            case 'pink':
            case 'pourpre':
            case 'purple':
            case 'rosa':
            case 'rose':
            case 'violet':
            case 'violett':
                $colour = 3;
                break;
            case 'black':
            case 'blanc':
            case 'grau':
            case 'gray':
            case 'grey':
            case 'gris':
            case 'noir':
            case 'schwarz':
            case 'weiss':
            case 'white':
                // The grey pieces were originally white, and were changed to
                // dark grey after it turned out that white pieces were difficult
                // to distinguish visually from blank industry spaces. That's why
                // variations on "white" are recognised here.
                $colour = 4;
                break;
            case 'blau':
            case 'bleu':
            case 'blue':
            case 'me':
            case 'my':
            case 'myself':
            case 'own':
            case 'self':
                $colour = 7;
                break;
            case 'braun':
            case 'brown':
            case 'marron':
            case 'orphan':
                $colour = 8;
                break;
            case 'flip':
            case 'flipped':
            case 'scoring':
            case 'yes':
                $flipped = 1;
                break;
            case 'non':
            case 'nonscoring':
            case 'not':
            case 'un':
            case 'unflip':
            case 'unflipped':
            case 'unscoring':
                $flipped = 0;
                break;
        }
    }
    if ( $tiletype == 1 and $numcubes > 5 ) {
        $numcubes = 5;
    }
    if ( $tiletype != 1 and $tiletype != 2 ) {
        $numcubes = 0;
    }
    if ( $techlevel == 0 and
         ( $tiletype < 4 or $colour == 8 )
         ) {
        $techlevel = 1;
    }
    if ( $tiletype == 4 and $techlevel > 2 ) {
        $techlevel = 2;
    }
    if ( $numcubes > 0 or $techlevel == 0 ) {
        $flipped = 0;
    }
    $buildarray = array();
    switch ( $tiletype ) {
        case 1: $buildarray[] = 'coal'; break;
        case 2: $buildarray[] = 'iron'; break;
        case 3: $buildarray[] = 'port'; break;
        case 4: $buildarray[] = 'ship'; break;
    }
    if ( $techlevel != 1 ) {
        $buildarray[] = $techlevel;
    }
    switch ( $colour ) {
        case 0: $buildarray[] = 'red';    break;
        case 1: $buildarray[] = 'yellow'; break;
        case 2: $buildarray[] = 'green';  break;
        case 3: $buildarray[] = 'purple'; break;
        case 4: $buildarray[] = 'grey';   break;
        case 8: $buildarray[] = 'brown';  break;
    }
    if ( $flipped  ) { $buildarray[] = 'flip';        }
    if ( $numcubes ) { $buildarray[] = 'c'.$numcubes; }
    return implode(' ', $buildarray);
}

class block_sequence {

    protected $blocks, $block_open, $current_block;

    protected function __construct () {
        $this->blocks        = array();
        $this->block_open    = false;
        $this->current_block = -1;
    }

    public static function blank () { return new self(); }

    public function handle_tag ($tag) {
        if ( $this->block_open ) {
            $this->block_open = $this->blocks[$this->current_block]->handle_tag($tag);
        }
        if ( !$this->block_open ) {
            switch ( $tag ) {
                case '[code]':
                case '[pre]':
                case '[preformatted]':
                    $this->block_open = true;
                    $this->current_block++;
                    $this->blocks[$this->current_block] = code_block::blank();
                    break;
                case '[multiple_newline]':
                case '[single_newline]':
                    break;
                default:
                    $this->block_open = true;
                    $this->current_block++;
                    $this->blocks[$this->current_block] = paragraph::blank();
                    $this->blocks[$this->current_block]->handle_tag($tag);
            }
        }
    }

    public function handle_content ($content) {
        if ( $this->block_open ) {
            $this->blocks[$this->current_block]->handle_content($content);
        } else if ( $content != '' ) {
            $this->block_open = true;
            $this->current_block++;
            $this->blocks[$this->current_block] = paragraph::from_content($content);
        }
    }

    public function finalise ($flags) {
        $block_texts   = array();
        $block_types   = array();
        $block_lengths = array();
        for ($i=0; $i<=$this->current_block; $i++) {
            $this_block_data = $this->blocks[$i]->finalise($flags);
            if ( $this_block_data !== false ) {
                $block_texts[]   = $this_block_data[0];
                $block_types[]   = $this_block_data[1];
                $block_lengths[] = $this_block_data[2];
            }
        }
        return array($block_texts, $block_types, $block_lengths);
    }

}

class code_block {

    protected $text;

    protected function __construct () {
        $this->text = '';
    }

    public static function blank () { return new self(); }

    public function handle_tag ($tag) {
        if ( strtolower($tag) == '[/code]' ) { return false; }
        switch ( $tag ) {
            case '[multiple_newline]':
                $this->text .= "\n\n";
                break;
            case '[single_newline]':
                $this->text .= "\n";
                break;
            default:
                $this->text .= $tag;
        }
        return true;
    }

    public function handle_content ($content) {
        $this->text .= $content;
    }

    public function finalise ($flags) {
        global $Administrator, $EscapeSequencesA;
        $lines = explode("\n", $this->text);
        for ($i=0; $i<count($lines); $i++) {
            $lines[$i] = rtrim($lines[$i]);
            $linelength = strlen($lines[$i]);
            $real_chars_read = 0;
            for ($j=0; $j<$linelength; $j++) {
                $real_chars_read++;
                if ( $lines[$i][$j] == "\t" ) {
                    $real_chars_read += 7;
                }
                for ($k=0; $k<count($EscapeSequencesA); $k++) {
                    $ESA_len = strlen($EscapeSequencesA[$k]);
                    if ( substr($lines[$i], $j, $ESA_len) == $EscapeSequencesA[$k] ) {
                        $j += $ESA_len - 1;
                    }
                }
                if ( $real_chars_read >= MAX_CODE_LINE_LENGTH and
                     $linelength > $j + 1
                     ) {
                    $j++;
                    $lines[$i] = rtrim(substr($lines[$i], 0, $j)).
                                 "\n".
                                 substr($lines[$i], $j);
                    $real_chars_read = 0;
                    $linelength++;
                }
            }
        }
        $lines = trim(implode("\n", $lines), "\n");
        if ( !$Administrator or ~$flags & STR_PERMIT_ADMIN_HTML ) {
            $lines = htmlspecialchars($lines, ENT_COMPAT, 'UTF-8');
        }
        return array($lines, BLOCK_CODE_BLOCK, strlen($lines));
    }

}

class paragraph {

    protected $pieces, $piece_is_tag;

    protected function __construct () {
        $this->pieces       = array();
        $this->piece_is_tag = array();
    }

    public static function blank () { return new self(); }

    public static function from_content($content) {
        $me = new self();
        $me->handle_content($content);
        return $me;
    }

    public function handle_content ($content) {
        $this->pieces[] = str_replace("\t", ' ', $content);
        $this->piece_is_tag[] = false;
    }

    public function handle_tag ($tag) {
        $tag = strtolower($tag);
        switch ( $tag ) {
            case '[multiple_newline]':
            case '[code]':
            case '[pre]':
            case '[preformatted]':
                return false;
        }
        $this->pieces[] = str_replace(array(' ', '"', "'"), '', $tag);
        $this->piece_is_tag[] = true;
        return true;
    }

    public function finalise ($flags) {
        global $Administrator, $EscapeSequencesA;
        $colourtrans = array( '[brightred]'   => 'brightred'   ,
                              '[lightred]'    => 'brightred'   ,
                              '[red]'         => 'brightred'   ,
                              '[darkred]'     => 'darkred'     ,
                              '[brightblue]'  => 'brightblue'  ,
                              '[lightblue]'   => 'brightblue'  ,
                              '[blue]'        => 'brightblue'  ,
                              '[darkblue]'    => 'darkblue'    ,
                              '[navyblue]'    => 'darkblue'    ,
                              '[navy]'        => 'darkblue'    ,
                              '[brightgreen]' => 'brightgreen' ,
                              '[lightgreen]'  => 'brightgreen' ,
                              '[green]'       => 'brightgreen' ,
                              '[darkgreen]'   => 'darkgreen'   ,
                              '[yellow]'      => 'yellow'      ,
                              '[orange]'      => 'orange'      ,
                              '[purple]'      => 'purple'      ,
                              '[pink]'        => 'pink'        ,
                              '[gold]'        => 'gold'        ,
                              '[silver]'      => 'silver'      ,
                              '[grey]'        => 'silver'      ,
                              '[gray]'        => 'silver'
                              );
        $text = '';
        $visibletext = '';
        $open_tags = array();
        $checkedgames = array();
        $site_address_regex_start = preg_quote(SITE_ADDRESS, '/').
                                    '|'.
                                    preg_quote(substr(SITE_ADDRESS, 7), '/');
        for ($i=0; $i<count($this->pieces); $i++) {
            if ( $this->piece_is_tag[$i] ) {
                $this->pieces[$i] = preg_replace( '/\\A\\[colou?r=(.+)\\]\\Z/',
                                                  '$1',
                                                  $this->pieces[$i]
                                                  );
                    // I will be rewriting these once again as "[colour=...]" below,
                    // but doing this here means that I can minimise the amount of
                    // work I have to do in the "switch" statements.
                switch ( $this->pieces[$i] ) {
                    case '[tilepic]':
                        if ( $flags & STR_HANDLE_IMAGES ) {
                            $i++;
                            $absorption_startpoint = $i;
                            $pieces_absorbed       = 0;
                            $absorbed_text         = '';
                            if ( $i < count($this->pieces) ) {
                                while ( $this->pieces[$i] != '[/tilepic]' ) {
                                    $pieces_absorbed++;
                                    $absorbed_text .= $this->pieces[$i];
                                    $i++;
                                    if ( $i == count($this->pieces) ) {
                                        $this->pieces[] = '[/tilepic]';
                                        $this->piece_is_tag[] = true;
                                        break;
                                            // This "break" statement is not actually necessary,
                                            // but it is here as insurance against infinite loops
                                            // in case I later screw up when editing this script.
                                    }
                                }
                            }
                            array_splice( $this->pieces,
                                          $absorption_startpoint,
                                          $pieces_absorbed,
                                          tilepic_standardise($absorbed_text)
                                          );
                            array_splice( $this->piece_is_tag,
                                          $absorption_startpoint,
                                          $pieces_absorbed,
                                          false
                                          );
                        }
                        break;
                    case '[brightred]':
                    case '[lightred]':
                    case '[red]':
                    case '[darkred]':
                    case '[brightblue]':
                    case '[lightblue]':
                    case '[blue]':
                    case '[navyblue]':
                    case '[darkblue]':
                    case '[navy]':
                    case '[brightgreen]':
                    case '[lightgreen]':
                    case '[green]':
                    case '[darkgreen]':
                    case '[yellow]':
                    case '[orange]':
                    case '[purple]':
                    case '[pink]':
                    case '[gold]':
                    case '[silver]':
                    case '[grey]':
                    case '[gray]':
                        $this->pieces[$i] = $colourtrans[$this->pieces[$i]];
                        $this->piece_is_tag[$i] = 'colour';
                        break;
                    case '[/brightred]':
                    case '[/lightred]':
                    case '[/red]':
                    case '[/darkred]':
                    case '[/brightblue]':
                    case '[/lightblue]':
                    case '[/blue]':
                    case '[/navyblue]':
                    case '[/darkblue]':
                    case '[/navy]':
                    case '[/brightgreen]':
                    case '[/lightgreen]':
                    case '[/green]':
                    case '[/darkgreen]':
                    case '[/yellow]':
                    case '[/orange]':
                    case '[/purple]':
                    case '[/pink]':
                    case '[/gold]':
                    case '[/silver]':
                    case '[/grey]':
                    case '[/gray]':
                        $this->pieces[$i] = '[/colour]';
                        break;
                    case '[bold]':
                    case '[boldface]':
                    case '[bf]':
                    case '[heavy]':
                    case '[strong]':
                        $this->pieces[$i] = '[b]';
                        break;
                    case '[/bold]':
                    case '[/boldface]':
                    case '[/bf]':
                    case '[/heavy]':
                    case '[/strong]':
                        $this->pieces[$i] = '[/b]';
                        break;
                    case '[it]':
                    case '[italic]':
                    case '[italicise]':
                    case '[italicize]':
                    case '[italicised]':
                    case '[italicized]':
                    case '[slant]':
                    case '[slanted]':
                    case '[em]':
                    case '[emph]':
                    case '[emphasis]':
                        $this->pieces[$i] = '[i]';
                        break;
                    case '[/it]':
                    case '[/italic]':
                    case '[/italicise]':
                    case '[/italicize]':
                    case '[/italicised]':
                    case '[/italicized]':
                    case '[/slant]':
                    case '[/slanted]':
                    case '[/em]':
                    case '[/emph]':
                    case '[/emphasis]':
                        $this->pieces[$i] = '[/i]';
                        break;
                    case '[strike]':
                    case '[strikeout]':
                    case '[strikethrough]':
                    case '[lineout]':
                    case '[linethrough]':
                    case '[ruleout]':
                    case '[ruledout]':
                    case '[ruled]':
                    case '[del]':
                    case '[delete]':
                    case '[deleted]':
                        $this->pieces[$i] = '[s]';
                        break;
                    case '[/strike]':
                    case '[/strikeout]':
                    case '[/strikethrough]':
                    case '[/lineout]':
                    case '[/linethrough]':
                    case '[/ruleout]':
                    case '[/ruledout]':
                    case '[/ruled]':
                    case '[/del]':
                    case '[/delete]':
                    case '[/deleted]':
                        $this->pieces[$i] = '[/s]';
                        break;
                    default:
                        $mymatch = array();
                        if ( preg_match( '/\\A\\[brass=(.+)\\]\\Z/',
                                                $this->pieces[$i],
                                                $mymatch
                                                )
                                    ) {
                            $mymatch                = (int)$mymatch[1];
                            $checkedgames[]         = $mymatch;
                            $this->pieces[$i]       = $mymatch;
                            $this->piece_is_tag[$i] = 'brass';
                        }
                }
            } else {
                $myarray = preg_split( '/('.$site_address_regex_start.')(board|lobby|decide).php\\?GameID=(\\d+)/',
                                       $this->pieces[$i],
                                       null,
                                       PREG_SPLIT_DELIM_CAPTURE
                                       );
                $myarraysize = count($myarray);
                if ( $myarraysize > 1 ) {
                    $splice_pieces       = array();
                    $splice_piece_is_tag = array();
                    for ($j=0; $j<$myarraysize; $j++) {
                        switch ( $j % 4 ) {
                            case 0:
                                $splice_pieces[]       = $myarray[$j];
                                $splice_piece_is_tag[] = false;
                                break;
                            case 3:
                                $mymatch               = (int)$myarray[$j];
                                $splice_pieces[]       = $mymatch;
                                $splice_pieces[]       = '[/brass]';
                                $splice_piece_is_tag[] = 'brass';
                                $splice_piece_is_tag[] = true;
                                $checkedgames[]        = $mymatch;
                        }
                    }
                    array_splice($this->pieces      , $i, 1, $splice_pieces      );
                    array_splice($this->piece_is_tag, $i, 1, $splice_piece_is_tag);
                    $i += count($splice_pieces);
                }
            }
        }
        $checkedgames = array_unique($checkedgames);
        if ( count($checkedgames) ) {
            while ( count($checkedgames) > MAX_GAME_STATUS_CHECKS ) {
                array_pop($checkedgames);
            }
            $QR = dbquery( DBQUERY_READ_RESULTSET,
                           'SELECT "GameID", "GameStatus" FROM "Game" WHERE "GameID" IN ('.implode(', ', $checkedgames).')'
                           );
            $checkedgames_statuses = array();
            while ( $row = db_fetch_assoc($QR) ) {
                switch ( $row['GameStatus'] ) {
                    case 'Cancelled':
                        $checkedgames_statuses[(int)$row['GameID']] = GAMELINK_DOES_NOT_EXIST;
                        break;
                    case 'Recruiting':
                        $checkedgames_statuses[(int)$row['GameID']] = GAMELINK_LOBBY;
                        break;
                    default:
                        $checkedgames_statuses[(int)$row['GameID']] = GAMELINK_BOARD;
                }
            }
            for ($i=0; $i<count($checkedgames); $i++) {
                if ( !array_key_exists($checkedgames[$i], $checkedgames_statuses) ) {
                    $checkedgames_statuses[$checkedgames[$i]] = GAMELINK_DOES_NOT_EXIST;
                }
            }
        }
        for ($i=0; $i<count($this->pieces); $i++) {
            if ( $this->piece_is_tag[$i] === 'brass' and
                 !in_array('brass', $open_tags)
                 ) {
                $visibletext = 'X';
                if ( array_key_exists($this->pieces[$i], $checkedgames_statuses) ) {
                    switch ( $checkedgames_statuses[$this->pieces[$i]] ) {
                        case GAMELINK_LOBBY:
                            if ( $flags & STR_DISREGARD_GAME_STATUS ) {
                                $text .= '[brass=-'.$this->pieces[$i].']';
                                $open_tags[] = 'brass';
                                break;
                            }
                        case GAMELINK_BOARD:
                            $text .= '[brass='.$this->pieces[$i].']';
                            $open_tags[] = 'brass';
                            break;
                        default:
                            $text .= '(Game '.$this->pieces[$i].' doesn\'t exist!) ';
                    }
                } else {
                    $text .= SITE_ADDRESS.'decide.php?GameID='.$this->pieces[$i];
                }
            } else if ( $this->piece_is_tag[$i] === 'colour' and
                        !in_array('colour', $open_tags) and
                        !in_array('brass', $open_tags)
                        ) {
                $text .= '[colour='.$this->pieces[$i].']';
                $open_tags[] = 'colour';
            } else if ( $this->piece_is_tag[$i] ) {
                switch ( $this->pieces[$i] ) {
                    case '[tilepic]':
                    case '[/tilepic]':
                        $text .= $this->pieces[$i];
                        break;
                    case '[b]':
                        if ( !in_array('b', $open_tags) ) {
                            $text .= '[b]';
                            $open_tags[] = 'b';
                        }
                        break;
                    case '[i]':
                        if ( !in_array('i', $open_tags) ) {
                            $text .= '[i]';
                            $open_tags[] = 'i';
                        }
                        break;
                    case '[s]':
                        if ( !in_array('s', $open_tags) ) {
                            $text .= '[s]';
                            $open_tags[] = 's';
                        }
                        break;
                    case '[/brass]':
                        while ( in_array('brass', $open_tags) ) {
                            $text .= '[/'.array_pop($open_tags).']';
                        }
                        break;
                    case '[/colour]':
                        while ( in_array('colour', $open_tags) ) {
                            $text .= '[/'.array_pop($open_tags).']';
                        }
                        break;
                    case '[/b]':
                        while ( in_array('b', $open_tags) ) {
                            $text .= '[/'.array_pop($open_tags).']';
                        }
                        break;
                    case '[/i]':
                        while ( in_array('i', $open_tags) ) {
                            $text .= '[/'.array_pop($open_tags).']';
                        }
                        break;
                    case '[/s]':
                        while ( in_array('s', $open_tags) ) {
                            $text .= '[/'.array_pop($open_tags).']';
                        }
                        break;
                    case '[single_newline]':
                        $current_text_length = strlen($text);
                        if ( $current_text_length and $text[strlen($text)-1] !== ' ' ) {
                            $text .= ' ';
                        }
                        break;
                    default:
                        if ( $this->pieces[$i] == '[coalcube]' or
                             $this->pieces[$i] == '[ironcube]' or
                             in_array($this->pieces[$i], $EscapeSequencesA) ) {
                            $visibletext = 'X';
                            $text .= $this->pieces[$i];
                        }
                }
            } else {
                $visibletext .= $this->pieces[$i];
                $text .= $this->pieces[$i];
            }
        }
        if ( trim($visibletext) === '' ) { return false; }
        while ( count($open_tags) ) {
            $text .= '[/'.array_pop($open_tags).']';
        }
        $text = trim(preg_replace('/\\s{2,}/', '  ', $text));
        if ( !$Administrator or ~$flags & STR_PERMIT_ADMIN_HTML ) {
            $text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
        }
        return array($text, BLOCK_PARAGRAPH, strlen($text));
    }

}

// STR_GPC                              1
// STR_ENSURE_ASCII                     2
// STR_TO_UPPERCASE                     4
// STR_TO_LOWERCASE                     8
// STR_NO_TRIM                         16
// STR_NO_STRIP_CR                     32
// STR_ESCAPE_HTML                     64
// STR_STRIP_TAB_AND_NEWLINE          128
// STR_CONVERT_ESCAPE_SEQUENCES       256
// STR_PERMIT_FORMATTING              512
// STR_HANDLE_IMAGES                 1024
// STR_PERMIT_ADMIN_HTML             2048
// STR_DISREGARD_GAME_STATUS         4096
// STR_EMAIL_FORMATTING              8192
// STR_MULTIBYTE_LENGTH_CONSTRAINTS 16384
function sanitise_str_fancy ($x, $minlength = null, $maxlength = null, $flags = 0) {
    global $Administrator, $EscapeSequencesA, $EscapeSequencesB;
    $x = (string)$x;
    if ( $flags & STR_GPC and
         PHP_MAJOR_VERSION < 6 and
         get_magic_quotes_gpc()
         ) {
        $x = stripslashes($x);
    }
    if (  $flags & STR_ENSURE_ASCII ) { $x = ensure_valid_ascii($x);    }
    else                              { $x = ensure_valid_utf8($x);     }
    if (  $flags & STR_TO_UPPERCASE ) { $x = strtoupper($x);            }
    if (  $flags & STR_TO_LOWERCASE ) { $x = strtolower($x);            }
    if ( ~$flags & STR_NO_TRIM      ) { $x = trim($x);                  }
    if ( ~$flags & STR_NO_STRIP_CR or
          $flags & ( STR_PERMIT_FORMATTING | STR_EMAIL_FORMATTING )
         ) {
        $x = str_replace("\r", '', $x);
    }
    if ( $flags & STR_EMAIL_FORMATTING ) {
        $x = htmlspecialchars($x, ENT_COMPAT, 'UTF-8');
        if ( $flags & STR_CONVERT_ESCAPE_SEQUENCES ) {
            $x = preg_replace( '/\\[\\s*([a-zA-Z]+)\\s*\\]/',
                               '[$1]',
                               $x
                               );
        }
        $x = preg_replace( '/(\\s*?\\n){2,}\\s*/',
                           '</p><p>',
                           $x
                           );
        $x = str_replace("\n", ' ', $x);
        $x = trim(preg_replace('/\\s{2,}/', '  ', $x));
        $x = '<p>'.$x.'</p>';
    } else if ( $flags & STR_PERMIT_FORMATTING ) {
        // NB. HTML escaping is carried out in the class methods called by the following
        // code (unless the user is an Administrator and the appropriate flag is set)
        $x = preg_replace( '/\\n{2,}/',
                           '[multiple_newline]',
                           $x
                           );
        $x = str_replace("\n", '[single_newline]', $x);
        $x = preg_replace( '/(\\[[^\\]])\\[(multiple|single)_newline\\]\\]/',
                           '$1',
                           $x
                           );
        $x = preg_split( '/(\\[.+?\\])/',
                         $x,
                         null,
                         PREG_SPLIT_DELIM_CAPTURE
                         );
        $num_pieces = count($x);
        $mymsg = block_sequence::blank();
        for ($i=0; $i<$num_pieces; $i++) {
            if ( $i % 2 ) {
                $mymsg->handle_tag($x[$i]);
            } else {
                $mymsg->handle_content($x[$i]);
            }
        }
        $x = msg_serialise($mymsg->finalise($flags));
        $y = $x[0];
        $x = $x[1];
    } else if ( $flags & STR_ESCAPE_HTML and
                ( !$Administrator or ~$flags & STR_PERMIT_ADMIN_HTML )
                ) {
        $x = htmlspecialchars($x, ENT_COMPAT, 'UTF-8');
    }
    if ( $flags & STR_CONVERT_ESCAPE_SEQUENCES ) {
        $x = str_ireplace($EscapeSequencesA, $EscapeSequencesB, $x);
    }
    if ( $flags & STR_STRIP_TAB_AND_NEWLINE ) {
        $x = str_replace(array("\n","\t"), '', $x);
    }
    if ( !is_null($minlength) and
         ( ( $flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
             mb_strlen($x, 'UTF-8') < $minlength
             ) or
           ( ~$flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
             strlen($x) < $minlength
             )
           )
         ) {
        $lengthindicator = -1;
    } else if ( !is_null($maxlength) and
                ( ( $flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
                    mb_strlen($x, 'UTF-8') > $maxlength
                    ) or
                  ( ~$flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
                    strlen($x) > $maxlength
                    )
                  )
                ) {
        $lengthindicator = 1;
    } else {
        $lengthindicator = 0;
    }
    if ( $flags & STR_PERMIT_FORMATTING ) {
        $x = $y.$x;
        if ( !is_null($maxlength) and
             ( ( $flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
                 mb_strlen($x, 'UTF-8') > 1.1 * $maxlength
                 ) or
               ( ~$flags & STR_MULTIBYTE_LENGTH_CONSTRAINTS and
                 strlen($x) > 1.1 * $maxlength
                 )
               )
             ) {
            $lengthindicator = 1;
        }
    }
    if (is_null($minlength) and is_null($maxlength)) {
        return $x;
    } else {
        return array($x, $lengthindicator);
    }
}

?>