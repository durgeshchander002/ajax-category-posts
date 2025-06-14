# AJAX Category Posts

Display posts from a specific WordPress category using AJAX-based pagination. Easily embeddable via a shortcode.

## ✨ Features

- 📄 Load category posts dynamically via AJAX
- 🔁 Pagination loads more posts without page reload
- ⚡ Lightweight, uses native WordPress AJAX API
- 🔌 Easy to implement via shortcode

---

## 🔧 Shortcode Usage

```php
[ajax_category_posts category="your-category-slug" posts_per_page="6"]
```

**Attributes:**
- `category` — Required. The slug of the category to fetch posts from.
- `posts_per_page` — Optional. Defaults to 6.

---

## 📦 Installation

1. Upload the plugin folder to `/wp-content/plugins/ajax-category-posts/`
2. Activate it via **Plugins > Installed Plugins**
3. Add the shortcode `[ajax_category_posts category="news"]` to any page or post
4. Make sure `ajax-category-posts.js` is correctly enqueued in your theme or plugin folder

---

## 🔄 How It Works

- The shortcode renders the first set of posts.
- When the user clicks "Load More", JavaScript sends an AJAX request.
- The plugin fetches the next set of posts server-side and appends them dynamically.

---

## 📁 JavaScript Dependencies

Ensure `ajax-category-posts.js` is present in your plugin folder and properly enqueued.

It should contain code to:
- Trigger AJAX call on button click
- Append the results to the existing container
- Handle pagination state

---

## 📝 Example Template Output

Each post is typically displayed as:

```html
<div class="ajax-post">
  <a href="post-link">Post Title</a>
</div>
```

You can customize the HTML output inside the `render_posts()` method.

---

## 🛠 Developer Notes

- PHP Hook: `wp_ajax_load_category_posts` & `wp_ajax_nopriv_load_category_posts`
- Script handle: `ajax-category-posts`
- Localized var: `ajax_cat_posts.ajax_url`

---

## 🛡 License

Licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).