# Nofollow Post Links

## Description

Nofollow Post Links is a plugin that allows you to manage the nofollow attribute of post links. You can manage them granularly, within the single post, or you can assign nofollow attribute to all links, or again, you can remove the attribute for all links.

## Compatibility and support

This plugin was designed specifically for ClassicPress. However, it may also be compatible with WordPress if the classic editor is used. In any case, support is limited to ClassicPress installations only.

## Installation

1. Upload plugin in the wp-content > plugins directory.
2. Then, install it.
3. Activate.

## Thirdy part assets

Nofollow Post Links uses:
- Update client class from plugin (Update Manager)[https://software.gieffeedizioni.it/plugin/update-manager/], released under GPLv2 license. Author Simone Fioravanti.
- github workflows by Simone Fioravanti https://github.com/xxsimoxx

## Frequently Asked Questions

### *Can I choose the links to which I want to nofollow?*
Yes, for each post you can establish which links should have nofollows. 
You can also globally determine which domains will not be nofollowed.

### *Is the nofollow attribution definitive?*
Here we must distinguish between attribution of nofollow on a "single content" and attribution of nofollow on "all contents" for every post type in your site:
- **Attribution on single content**. Here the attribution of nofollow or its removal occurs by modifying the content post unless you have set or left the "Same for the metabox action" option set. In this case, see the next point.
- **Global attribution**. Global attribution works in three ways: "Javascript", "Content Filter" and "Content Edit". If you use the "Javascript" or "Content Filter" mode, the original content of the **post is not changed**. With the "Content Edit" mode, the plugin will **write** the content of your post. If you choose the "Content Edit" option, remember to make a backup of your database before proceeding.

### *What is the purpose of Restore Post Settings?*
"Restore post setting" is an action that allows the restoration of the configuration via metabox, before the **global action** is run. This action is particularly useful if you use the "Content Edit" mode. However, please note that if you save the content of your post after a global action in "content edit" mode, a new backup will be generated with the globally assigned settings.

### *Should I make a database backup before using Nofollow Post Links?*
Generally, it is good practice to make a backup before installing and using a plugin on a live site. In the case of Nofollow Post Links, backup is **highly recommended** if you use the "Content Edit" mode for global actions. This mode is still **experimental**, and is highly invasive, because **edit the post**, and since the plugin has not been sufficiently tested on a sufficient number of installations, it becomes essential to create a backup (at least of the wp_posts and wp_postmeta tables.) before executing it, also because the "Restore post settings" action may not be useful in this case.

### *What is the best mode for global action?*
Please note that the "Javascript" mode and the "Content Filter" mode work in the frontend, therefore, although minimal, they will have an impact on the loading of the page. But if you use a caching system, you won't even notice.
The "Content Edit" mode has no impact on the frontend, because it only edits the post.
The choice is therefore subjective, without prejudice to what has been said in the previous FAQs.

### *Wait! When I started a global action the metabox disappeared!*
Naturally. If you have activated a global dofollow or nofollow action you cannot edit links via the metabox.

### *What is nofollow mapping?*
Nofollow mapping allows you to retrieve nofollows manually inserted into links and update metabox settings. Very useful especially after installing the plugin.

### *What is the function of harmonize?*
Harmonize allows you to simultaneously assign the nofollow attribute to a specific domain or remove the nofollow attribute. It is a global assignment function which however only acts on a specific domain.

### *Can I create a domains whitelist?*
Yes. Simply enter them in the appropriate field in the settings tab, and the global actions will ignore those domains. Additionally, you won't be able to assign nofollow via the metabox. However, in content edit mode you can assign it manually (but why would you do that?).

### *Can I choose the post type for global actions?*
Yes. From the settings, you can determine which post types you can perform global actions on. You can also decide in which post types to show the post link metabox.

### *What happens when the plugin is uninstalled?*
Any data in the database referring to the plugin will be removed. If you used content edit mode to assign nofollows, they will remain after uninstallation unless you used "Restore Post Settings" or the "Global Dofollow" action before uninstalling the plugin.