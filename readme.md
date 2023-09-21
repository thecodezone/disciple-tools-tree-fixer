Tree Fixer
----------
A Disciple.tools extension to fix the tree structure of groups and contacts. Use this extension to fix issues with Genmapper trees. 

## Fixes

### Groups
- **Circular references:** Fixes parent / child circular references by removing the child group. 
- **Orphaned records:** Fixes orphaned groups by removing and readding children and parent groups.

### Contacts
- **Circular references:** Fixes coaching tree circular references by removing the coached_by contact.
- **Orphaned records:** Fixes orphaned contacts by removing and readding coaches and coached by.

## Usage
1. Install the extension.
2. Visit WordPress Admin > Extensions > Tree Fixer.
3. Use the "Groups" or "Contacts" tabs to switch between the two.
4. Click the "Fix" button
5. Press "Stop" to stop the fix if needed.

## Warning
> This plugin is not yet stable. It is recommended to backup your database before using this plugin. Fixes are permanent and cannot be reversed. 