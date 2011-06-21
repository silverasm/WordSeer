/**********************************************************************
	wordtree.js
	Utilities for turning a list of sentences into a graphical display
**********************************************************************/

/** makes a tree out of either a left or a right context
	@param context : a list of strings (sentences);
 **/
function makeTree(context, level, detail, orientation){
	var tree = {};
	var first, sentence, max, collapsed, subtree, key ;
	max = 0;
	var keys = [];
	for(var i = 0; i < context.length; i++){
		sentence = context[i].sentence;
		if(sentence.length > 0){
			first = sentence[0];
			if(!first){
				sentence = sentence.slice(1);
				first = sentence[0]
			}
			if(first){
				first = first.toLowerCase();
				if(tree[first]== undefined){
					tree[first] = {key:first, after:[],level:level, count:1, tree:{}, ids:[]};
				}
				tree[first].after.push({id:context[i].id, sentence:sentence.slice(1)});
				tree[first].ids.push(context[i].id);
				tree[first].count += 1;
			} 
		}
	}
	for(var key in tree){
		if(tree[key].count > max){
			max = tree[key].count;
		}
		keys.push(key);	
	}
	for(var i = 0 ; i < keys.length; i++){
		key = keys[i];
		if(tree[key].count > (max*(100-detail)/100)){
			if(tree[key].after.length > 1){
				subtree = makeTree(tree[key].after, level+1, detail, orientation);
				collapsed = collapse(subtree, key, orientation);
				tmp = tree[key];
				delete tree[key];
				tree[collapsed.key] = {};
				tree[collapsed.key]["after"] = tmp["after"];
				tree[collapsed.key]["count"] = tmp["count"];
				tree[collapsed.key]["level"] = tmp["level"];
				tree[collapsed.key]["ids"] = tmp["ids"];
				tree[collapsed.key]["tree"] = collapsed.tree;
				tree[collapsed.key]["key"] = collapsed.key;
			}
		}else{
			delete tree[key];
		}
	}
	return sort(tree);
}

function compareSubTrees(t1, t2){
	return t2.count - t1.count;
}

function sort(tree){
	var tmp = [];
	for(key in tree){
		tmp.push(tree[key]);
	}
	tmp.sort(compareSubTrees);
	return tmp
}

function collapse(tree, key, orientation){
	if(tree.length == 1){
		for(k in tree){
			if(orientation == WordTree.RO_LEFT){
				return collapse(tree[k].tree, key+" "+tree[k].key);
			}else if(orientation = WordTree.RO_RIGHT){
				return collapse(tree[k].tree, tree[k].key+" "+key);
			}
		}
	}else{
		return {tree:tree, "key":key}
	}
}

function size(count, level){
	if(count == "end"){
		return 10;
	}else{
		return Math.min(30, Math.max(12, (12+count)/(Math.log(level+1))));
	}
}
/** 
@param context: the name of the root node 
**/
var nodeID;
function displayTree(context, tree, container, width, height, direction, paper){
	var wordTree = new WordTree("tree", container, width, height, paper);
	wordTree.config.iRootOrientation = direction;
	var queue = []
	nodeID = 0;
	wordTree.add(nodeID/*root id*/, -1, context, 25, "black", []/*sentence id's*/);
	nodeID += 1;
	addAll(tree, wordTree, 0/*parent*/);
	wordTree.UpdateTree();
	return wordTree;
}

function addAll(tree, wordTree, parent){
	var key;
	var dsc;
	for(var i = 0; i < tree.length; i++){
		key = tree[i].key;
		wordTree.add(nodeID, parent, key, size(tree[i].count, tree[i].level), "black", tree[i].ids);
		nodeID+=1;
		if(tree[i].after.length > 1){
			addAll(tree[i].tree, wordTree, nodeID-1, "black");
		}else{
			dsc = "";
			if(wordTree.config.iRootOrientation == WordTree.RO_LEFT){
				dsc = tree[i].after[0].sentence.join(" ")
			}else{
				dsc = tree[i].after[0].sentence.reverse().join(" ");
			}
			wordTree.add(nodeID, nodeID-1, dsc, size("end", ""), "#999", tree[i].ids);
			nodeID +=1;
		}
	}
}

function makeContext(data,type, which){
		return data[type][which]
}

function makeWordTree(sentences, context, detail, container, width, height, direction, paper){
	var tree = makeTree(sentences, 1, detail, direction);
	return displayTree(context, tree, container, width, height, direction, paper)
}