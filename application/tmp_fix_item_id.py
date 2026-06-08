import os
# We're running from the application/ directory
base = os.path.dirname(os.path.abspath(__file__))
filepath = os.path.join(base, 'controllers', 'Items.php')

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old = '''$item_id = isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id;

\t\t\tif(isset($tags))'''

new = '''$item_id = isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id;

\t\t\t//Guard: if we still don't have an item_id, skip this row (prevents "Column 'item_id' cannot be null" errors)
\t\t\tif (!$item_id)
\t\t\t{
\t\t\t\t$this->_log_validation_error($i+2, lang('items_unable_to_create_item'));
\t\t\t\t$can_commit = FALSE;
\t\t\t\tcontinue;
\t\t\t}

\t\t\tif(isset($tags))'''

if old in content:
    content = content.replace(old, new, 1)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print('REPLACEMENT SUCCESSFUL')
else:
    print('NOT FOUND')
    idx = content.find("$item_id = isset($item_data")
    if idx >= 0:
        print('Found at', idx)
        print('Context:', repr(content[idx:idx+180]))
    else:
        idx2 = content.find('isset($item_data')
        if idx2 >= 0:
            print('Found isset at', idx2)
            print('Context:', repr(content[idx2-50:idx2+150]))
        else:
            print('Could not find the string at all')
