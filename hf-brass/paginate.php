<?php

// $PageParameters (the fourth argument) should be a SANITISED array of
// relevant variables to go into a GET variable list. Alternatively, it may be
// null, which is equivalent to passing an empty array. The result of passing
// another datatype is undefined, although at the time of writing this comment
// it is equivalent to passing null or an empty array.

function paginationbar ( $ObjectDescriptionSingular,
                         $ObjectDescriptionPlural,
                         $PageURL,
                         $PageParameters,
                         $NumberPerPage,
                         $ThisPage,
                         $NumberOfItems
                         ) {

    $output = fragment::blank();
    if ( $NumberOfItems == 0 ) {
        return array($output, $output);
    }
    $output->opennode('p');

    $PageURLWithParameters = $PageURL.'?';
    if ( is_array($PageParameters) ) {
        foreach ( $PageParameters as $key => $value ) {
            $PageURLWithParameters .= $key.'='.$value.'&amp;';
        }
    }
    $PageURLWithParameters .= 'Page=';
    $NoSecondBar = false;
    $NumberOfPages = $NumberOfItems/$NumberPerPage;
    $NumberOfPages = (int)$NumberOfPages;
    if ( $NumberOfItems % $NumberPerPage ) { $NumberOfPages++; }

    if ( $NumberOfPages == 1 ) {
        if ( $NumberOfItems == 1 ) {
            $IsAre = 'is';
        } else {
            $IsAre = 'are';
            $ObjectDescriptionSingular = $ObjectDescriptionPlural;
        }
        $output->text( 'This script displays up to '.
                       $NumberPerPage.' '.$ObjectDescriptionPlural.
                       ' to a page.'
                       );
        $output->text( '<br>There '.
                       $IsAre.' '.
                       $NumberOfItems.' '.$ObjectDescriptionSingular.
                       ' to display, which is only one page\'s worth.'
                       );
        if ( $ThisPage != 1 ) {
            $NoSecondBar = true;
            $output->text( '<br><span class="font_sans_serif" style="font-weight: bold;"><a href="'.
                           $PageURLWithParameters.
                           '1">Go to page 1</a></span>'
                           );
        }
        $output->closenode();
    } else {
        if ( $ThisPage > $NumberOfPages ) {
            $NoSecondBar = true;
            $output->text( 'This script displays up to '.
                           $NumberPerPage.' '.$ObjectDescriptionPlural.
                           ' to a page.'
                           );
            $output->text( '<br>There are '.
                           $NumberOfItems.' '.$ObjectDescriptionPlural.
                           ' to display, which isn\'t enough to reach to the specified number of pages.'
                           );
            $output->emptyleaf('br');
            $output->opennode('span', 'class="font_sans_serif"');
            $output->leaf('b', 'Pages:');
            $output->opennode('ul', 'class="paginationlist"');
        } else {
            $output->text( 'This script displays up to '.
                           $NumberPerPage.' '.$ObjectDescriptionPlural.
                           ' to a page. There are '.
                           $NumberOfItems.' '.$ObjectDescriptionPlural.
                           ' to display.'
                           );
            $output->emptyleaf('br');
            $output->opennode('span', 'class="font_sans_serif"');
            $output->leaf('b', 'Pages:');
            $output->opennode('ul', 'class="paginationlist"');
            if ( $ThisPage > 1 ) {
                $output->leaf( 'li',
                               '<a href="'.
                                   $PageURLWithParameters.
                                   ($ThisPage-1).
                                   '">Previous</a>',
                               'class="separator_dash_after"'
                               );
            }
            if ( $ThisPage > 0 and $ThisPage < $NumberOfPages ) {
                $output->leaf( 'li',
                               '<a href="'.
                                   $PageURLWithParameters.
                                   '2">Next</a>',
                               'class="separator_dash_after"'
                               );
            }
            if ( $ThisPage < 1 ) { $NoSecondBar = true; }
        }
        $PagesWanted = array(1, 2);
        if ( $ThisPage <= $NumberOfPages ) {
            for ($i=$ThisPage-2; $i<$ThisPage+3; $i++) {
                if ( $i > 0 and
                     $i <= $NumberOfPages and
                     !in_array($i, $PagesWanted)
                     ) {
                    $PagesWanted[] = $i;
                }
            }
        }
        if ( !in_array($NumberOfPages-1, $PagesWanted) ) {
            $PagesWanted[] = $NumberOfPages - 1;
        }
        if ( !in_array($NumberOfPages, $PagesWanted) ) {
            $PagesWanted[] = $NumberOfPages;
        }
        for ($i=0; $i<count($PagesWanted)-1; $i++) {
            if ( $PagesWanted[$i+1] == $PagesWanted[$i] + 2 ) {
                for ($j=count($PagesWanted); $j>$i; $j--) {
                    $PagesWanted[$j] = $PagesWanted[$j-1];
                }
                $PagesWanted[$i+1] = $PagesWanted[$i] + 1;
            }
        }
        $MissingPages = false;
        for ($i=0; $i<count($PagesWanted); $i++) {
            if ( $PagesWanted[$i] == $NumberOfPages and
                 $MissingPages
                 ) {
                $attributes = 'class="separator_dash_after"';
            } else {
                $attributes = null;
            }
            if ( $PagesWanted[$i] == $ThisPage ) {
                $output->leaf( 'li',
                               '<b>'.$PagesWanted[$i].'</b>',
                               $attributes
                               );
            } else {
                $output->leaf( 'li',
                               '<b><a href="'.
                                   $PageURLWithParameters.
                                   $PagesWanted[$i].
                                   '">'.
                                   $PagesWanted[$i].
                                   '</a></b>',
                               $attributes
                               );
            }
            if ( $i != count($PagesWanted) - 1 and
                 $PagesWanted[$i+1] != $PagesWanted[$i] + 1
                 ) {
                $output->leaf('li', '&hellip;');
                $MissingPages = true;
            }
        }
        if ( $MissingPages ) {
            $output->opennode('li');
            $output->opennode( 'form',
                               'action="'.
                                   $PageURL.
                                   '" method="GET" style="display: inline;"'
                               );
            $output->text('Or enter a page number:');
            $output->emptyleaf( 'input',
                                'type="text" name="Page" size=5 maxlength=10'
                                );
            $output->emptyleaf('input', 'type="submit" value="Go"');
            if ( is_array($PageParameters) ) {
                foreach ( $PageParameters as $key => $value ) {
                    $output->emptyleaf( 'input',
                                        'type="hidden" name="'.
                                            $key.
                                            '" value="'.
                                            $value.'"'
                                        );
                }
            }
            $output->closenode(5); // form, li, ul, span, p
        } else {
            $output->closenode(3); // ul, span, p
        }
    }

    if ( $NoSecondBar ) {
        return array($output, fragment::blank());
    } else {
        return array($output, $output);
    }

}

?>