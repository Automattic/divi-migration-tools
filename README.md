# Divi Migration Tools

### Different criteria to migrate the Divi shortcodes. 

* Migrate the following Divi shortcodes to Gutenberg blocks 🙌

    * [et_pb_video]
        * It will pick the youtube url and create a youtube block then it will replace the shortcode with Gutenberg block inside the post-content.
        
    * [et_pb_button]
        * It will pick the {href, text color, background color} and create a button block then it will replace the shortcode with Gutenberg block inside the post-content.
        
    * [et_pb_image]
        * It will pick the {src, align attribute, height, width} and create a image block then it will replace the shortcode with Gutenberg block inside the post-content.

    * [et_pb_fullwidth_image]
        * It will pick the {src, align attribute, height, width} and create a image block then it will replace the shortcode with Gutenberg block inside the post-content.

    * [et_pb_post_title]
        * It will create a group block and nest the heading,image,paragraph blocks inside it then it will replace the shortcode with Gutenberg block inside the post-content.
        * This will be dependent on a post where shortcode is added. So it will behave like the_post() behaviour for single page.
        * This will pull the styles too. i.e. background-color, font-style, font-size, line-height etc.  

    * [et_pb_divider]
        * It will pick the {color} and create a shape divider block then it will replace the shortcode with Gutenberg block inside the post-content.

    * [et_pb_blurb]
        * It will pick the {image src, align attributes, paragraph test} and create a image block + heading block then it will replace the shortcode with Gutenberg block inside the post-content.
---

* Skip the following Divi shortcodes to Gutenberg blocks ⚠️

    * [embed], [caption], [toc], [Sarcastic], [gallery], [Tweet], [Proof]
        * These shortcode doesn't have any dependency on Divi.

    * [et_social_follow]
        * This shortcode is coming though a plugin called `Monarch` and it will work independently without Divi
 
    * [et_pb_tabs], [et_pb_tab], [et_pb_testimonial], [et_pb_contact_form] , [et_pb_contact_field], [et_pb_video_slider], [et_pb_video_slider_item], [et_pb_social_media_follow], [et_pb_social_media_follow_network], [et_pb_blog], [et_pb_pricing_tables], [et_pb_team_member], [et_pb_pricing_tables]
        * These shortcodes needs to be fixed manually by adding needful content markup.

---

* Divi shortcodes that can be cleared from the post-content 🧐 [Most crucial and important for manual review post script work]

    * [et_pb_section], [et_pb_row], [et_pb_column], [et_pb_text], [et_pb_fullwidth_header], [et_pb_code], [et_pb_cta], [et_pb_row_inner], [et_pb_column_inner], [et_pb_sidebar], [et_pb_slider], [et_pb_slide], [et_pb_line_break_holder], [et_pb_toggle], [et_pb_fullwidth_code]
        * These shortcodes can be cleared from markup but again manual verification is required.
    

### Migration Commands:

* wp divi-cli migrate-shortcodes
    * This command will start migration posts for posts in dry-run mode.
    * It will keep the copy of old Divi post-content in post-meta.
    * Flags:
        * --post-type => Value can be post or page or cpt.
        * --status => Value can be publish or draft.
        * --dry-run => Value can be true or false.

* wp divi-cli reset-divi-content
    * This command will revert the migration and will replace the original Divi content.
    * It is possible because before migration, Divi content is being stored in postmeta as copy of old Divi content.
    * Flags:
        * --post-type => Value can be post or page or cpt.
        * --status => Value can be publish or draft.
        * --dry-run => Value can be true or false.
    
### Migration logs

* Above command will create a logs inside the uploads directory.
* path of logs will be `~/uploads/divi-migration-logs/`.
* File names will be `divi-shortcode-logs-{post-status}-{post-type}.csv`. based on post-status and post-type will be passed while calling the command
* I.e. https://example.com/wp-content/uploads/divi-migration-logs/divi-shortcode-logs-publish-post.csv