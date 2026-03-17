# StoryFactory

**StoryFactory** is a minimalist, high-performance web application designed for visual storytellers. It allows you to create individual, image-led stories with ease. 

Unlike traditional CMS platforms, StoryFactory uses a **Flat-File Architecture**—meaning no SQL databases are required. Everything is handled via PHP, JSON, and local directories.

## Key Features

* **Zero-Config Setup:** No database to migration or SQL to import. Just upload and run.
* **Automatic Story Generation:** Create a new story folder, and the app automatically clones the necessary index, upload, and admin templates.
* **Custom Cover Selection:** Choose any uploaded image to represent your story in the main gallery.
* **Industrial UI:** A sleek, high-contrast "Safety Orange" and "Carbon Grey" aesthetic.
* **Mobile-Ready:** Fully responsive administration and viewing experience.
* **Date Tracking:** Automatically logs and displays the creation date for every story.

## Project Structure

* `/index.php` - The main public gallery (The Hub).
* `/hub.php` - The admin login gateway.
* `/stories/` - Where your created stories live.
* `/template/` - The blueprint files used when a new story is generated.

## How to Use

1.  Login via the **Admin Access** (hub.php).
2.  Enter a title to **Create a New Story**. A folder is automatically generated.
3.  Click **Admin** on the new story card to upload your images and text.
4.  Use the **"Set Cover"** button in the story admin to choose which image appears in the main factory gallery.
