# text-post
Plugin Name: Multi-File Text Upload   Description: Allows the user to upload multiple .txt and .md files and create new posts from them.

## Installation

Make folder

`wp-content\plugins\text-post`

Copy the php file into that folder.

## Usage

Go to Admin panel / Tools / Multi-File Text upload.

- Choose the Files
- Choose the category (empty categories don't show on the list)
- Choose the author
- Choose the post status

Click Upload Files

Posts are created and a list of permalinks is shown.

## File format

The plugin expects .txt or .md files.

The first line of the file will be used as the blog post title / headline.

The second line should be blank.

Content starts at the third line, except if the third line is identical to the headline in which case it is removed.

Only markdown subheading formatting (`###`) is parsed into tags (`<h3>`).

