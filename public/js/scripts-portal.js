function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

// BEEP js 
// Dynamic Tree
$(function () {
  // Taxonomy editor
  $('#category-tree').on("changed.jstree", function (e, data) {
    id = null;

    if (typeof data.node != 'undefined') {
      href_arr = data.node.a_attr.href.split('/');
      id = href_arr[href_arr.length - 1];
    }

    parent_id = window.location.href.indexOf('parent_id=') > -1 ? window.location.href.split('parent_id=')[1] : null; //console.log(id, parent_id, data.node, window.location.href);

    if (data.action == 'select_node' && window.location.href != data.node.a_attr.href && (window.location.href.indexOf('/create') == -1 || id != parent_id && parent_id != null)) {
      window.open(data.node.a_attr.href, '_self');
    }
  }).jstree({
    "core": {
      "check_callback": checkCallback,
      "themes": {
        "variant": "small",
        "stripes": true
      }
    },
    "state": {
      "key": "main-category-tree"
    },
    "plugins": ["search", "state", "sort"]
  });
  var to = false;
  $('#category-tree-search').keyup(function () {
    if (to) {
      clearTimeout(to);
    }

    to = setTimeout(function () {
      var v = $('#category-tree-search').val();
      $('#category-tree').jstree(true).search(v);
    }, 250);
  });

  var getSelection = function getSelection(data) {
    var tree = data.instance.get_json(null, {
      "no_icon": true,
      "no_id": true,
      "no_data": true,
      "no_li_attr": true,
      "no_a_attr": true,
      "flat": true
    }); //console.log(tree);

    var cats = [];
    Object.entries(tree).forEach(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
          i = _ref2[0],
          item = _ref2[1];

      if (item.state.selected == 1) cats.push(item.state.cat);
    });
    $("input[id=categoryinput]").val(cats.join(','));
    console.log('Selected ' + cats.length + ' nodes', cats);
  }; // Checklist editor


  var checkDraggable = function checkDraggable(nodes, e) {
    var drag = nodes[0].parents.length > 2 ? false : true;
    return drag;
  };

  var checkCallback = function checkCallback(operation, node, node_parent, node_position, more) {
    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
    // in case of 'rename_node' node_position is filled with the new node name
    if (operation === "move_node") {
      if (node.parent === "#") // root item is dragged
        return node_parent.id === "#"; // and dropped on a root node
      else return node_parent.id === node.parent;
    }

    return false; //allow all other operations
  };

  $('#checklist-tree').on('changed.jstree', function (e, data) {
    getSelection(data); // var i, j, r = [];
    // for(i = 0, j = data.selected.length; i < j; i++) {
    //   r.push(data.instance.get_node(data.selected[i]).id);
    // }
    // console.log('Selected: ' + r.join(', '));
  }).on("select_node.jstree", function (e, data) {
    if (data.event) {
      data.instance.select_node(data.node.children_d);
    }
  }).on("deselect_node.jstree", function (e, data) {
    if (data.event) {
      data.instance.deselect_node(data.node.children_d);
    }
  }).on("move_node.jstree", function (e, data) {
    getSelection(data);
  }).jstree({
    "core": {
      "check_callback": checkCallback,
      "themes": {
        "variant": "small",
        "stripes": true
      }
    },
    "state": {
      "key": "main-checklist-tree"
    },
    "plugins": ["search", "checkbox", "dnd"],
    "checkbox": {
      "cascade": "undetermined",
      "three_state": false,
      "cascade_to_hidden": true,
      "keep_selected_style": true
    },
    "dnd": {
      "check_while_dragging": true,
      "drag_selection": false,
      "touch": true,
      "copy": false,
      "use_html5": false
    }
  });
  var to = false;
  $('#checklist-tree-search').keyup(function () {
    if (to) {
      clearTimeout(to);
    }

    to = setTimeout(function () {
      var v = $('#checklist-tree-search').val();
      $('#checklist-tree').jstree(true).search(v);
    }, 250);
  });
});
