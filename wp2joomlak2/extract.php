<?php

/**
 * Highly unstable and untested.
 */

/**
 * auto_p is nl2br on steroids.
 *
 * Credits go to the very clever Kohana developers.
 */
function auto_p($str, $br = TRUE)
{
    // Trim whitespace
    if (($str = trim($str)) === '')
        return '';

    // Standardize newlines
    $str = str_replace(array("\r\n", "\r"), "\n", $str);

    // Trim whitespace on each line
    $str = preg_replace('~^[ \t]+~m', '', $str);
    $str = preg_replace('~[ \t]+$~m', '', $str);

    // The following regexes only need to be executed if the string contains html
    if ($html_found = (strpos($str, '<') !== FALSE))
    {
        // Elements that should not be surrounded by p tags
        $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

        // Put at least two linebreaks before and after $no_p elements
        $str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
        $str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
    }

    // Do the <p> magic!
    $str = '<p>'.trim($str).'</p>';
    $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

    // The following regexes only need to be executed if the string contains html
    if ($html_found !== FALSE)
    {
        // Remove p tags around $no_p elements
        $str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
        $str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
    }

    // Convert single linebreaks to <br />
    if ($br === TRUE)
    {
        $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
    }

    return $str;
}

include 'wp-blog-header.php';
echo 'asdf';

$args = 'cat=27';
$order = 0;

// The Query
query_posts( $args );

$query = 'INSERT INTO `digipipe_enterprise`.`ent21_k2_items` (`id`, `title`, `alias`, `catid`, `published`, `introtext`, `fulltext`, `video`, `gallery`, `extra_fields`, `extra_fields_search`, `created`, `created_by`, `created_by_alias`, `checked_out`, `checked_out_time`, `modified`, `modified_by`, `publish_up`, `publish_down`, `trash`, `access`, `ordering`, `featured`, `featured_ordering`, `image_caption`, `image_credits`, `video_caption`, `video_credits`, `hits`, `params`, `metadesc`, `metadata`, `metakey`, `plugins`, `language`) VALUES
'."\n";

// The Loop
while ( have_posts() ) : the_post();
    echo '<li>';
    the_title();
    //var_dump($post);
    //$category = get_the_category();
    //echo $category[0]->name;
    //echo $category[0]->cat_ID;
    echo '</li>';
    $query .= '(';
    $query .= 'NULL, '; # id
    $query .= '\''. mysql_real_escape_string($post->post_title) .'\', '; # title
    $query .= '\''. mysql_real_escape_string($post->post_name) .'\', '; # alias
    $query .= '14, '; # catid
    $query .= '1, '; # published
    $query .= '\''. mysql_real_escape_string(auto_p($post->post_content)) .'\', '; # introtext
    $query .= '\'\', '; # fulltext
    $query .= 'NULL, '; # video
    $query .= 'NULL, '; # gallery
    $query .= '\'[]\', '; # extra_fields
    $query .= '\'\', '; # extra_fields_search
    $query .= '\''. mysql_real_escape_string($post->post_date) .'\', '; # created
    $query .= '311, '; # created_by
    $query .= '\'\', '; # created_by_alias
    $query .= '0, '; # checked_out
    $query .= 'NOW(), '; # checked_out_time
    $query .= 'NOW(), '; # modified
    $query .= '311, '; # modified_by
    $query .= 'NOW(), '; # publish_up
    $query .= '\'0000-00-00 00:00:00\', '; # publish_down
    $query .= '0, '; # trash
    $query .= '1, '; # access
    $query .= (int) $order .', '; # ordering
    $query .= '0, '; # featured
    $query .= '0, '; # featured_ordering
    $query .= '\'\', '; # image_caption
    $query .= '\'\', '; # image_credits
    $query .= '\'\', '; # video_caption
    $query .= '\'\', '; # video_credits
    $query .= '0, '; # hits
    $query .= '\'{"catItemTitle":"","catItemTitleLinked":"","catItemFeaturedNotice":"","catItemAuthor":"","catItemDateCreated":"","catItemRating":"","catItemImage":"","catItemIntroText":"","catItemExtraFields":"","catItemHits":"","catItemCategory":"","catItemTags":"","catItemAttachments":"","catItemAttachmentsCounter":"","catItemVideo":"","catItemVideoWidth":"","catItemVideoHeight":"","catItemAudioWidth":"","catItemAudioHeight":"","catItemVideoAutoPlay":"","catItemImageGallery":"","catItemDateModified":"","catItemReadMore":"","catItemCommentsAnchor":"","catItemK2Plugins":"","itemDateCreated":"","itemTitle":"","itemFeaturedNotice":"","itemAuthor":"","itemFontResizer":"","itemPrintButton":"","itemEmailButton":"","itemSocialButton":"","itemVideoAnchor":"","itemImageGalleryAnchor":"","itemCommentsAnchor":"","itemRating":"","itemImage":"","itemImgSize":"","itemImageMainCaption":"","itemImageMainCredits":"","itemIntroText":"","itemFullText":"","itemExtraFields":"","itemDateModified":"","itemHits":"","itemCategory":"","itemTags":"","itemAttachments":"","itemAttachmentsCounter":"","itemVideo":"","itemVideoWidth":"","itemVideoHeight":"","itemAudioWidth":"","itemAudioHeight":"","itemVideoAutoPlay":"","itemVideoCaption":"","itemVideoCredits":"","itemImageGallery":"","itemNavigation":"","itemComments":"","itemTwitterButton":"","itemFacebookButton":"","itemGooglePlusOneButton":"","itemAuthorBlock":"","itemAuthorImage":"","itemAuthorDescription":"","itemAuthorURL":"","itemAuthorEmail":"","itemAuthorLatest":"","itemAuthorLatestLimit":"","itemRelated":"","itemRelatedLimit":"","itemRelatedTitle":"","itemRelatedCategory":"","itemRelatedImageSize":"","itemRelatedIntrotext":"","itemRelatedFulltext":"","itemRelatedAuthor":"","itemRelatedMedia":"","itemRelatedImageGallery":"","itemK2Plugins":""}\', '; # params
    $query .= '\'\', '; # metadesc
    $query .= '\'robots=author=\', '; # metadata
    $query .= '\'\', '; # metakey
    $query .= '\'\', '; # plugins
    $query .= '\'*\''; # language
    $query .= '),'."\n";
    $order++;
endwhile;

echo substr($query, 0, -2).';'."\n\n";

// Reset Query
wp_reset_query();
