/*-------------------------------------------------------------------------------------------
|     WordTree.js -- adapted fromEmilio Cortegoso Lobato's ECOTree.js by Aditi Muralidharan
| 	 	With RaphaelJS integration
|    	Last modified: April 8th, 2011
|--------------------------------------------------------------------------------------------
| (c) 2006 Emilio Cortegoso Lobato
|     
|     ECOTree is a javascript component for tree drawing. It implements the node positioning
|     algorithm of John Q. Walker II "Positioning nodes for General Trees".
|    
|     Basic features include:
|       - Layout features: Different node sizes, colors, link types, alignments, separations
|                          root node positions, etc...
|       - Nodes can include a title and an hyperlink, and a hidden metadata.
|       - Subtrees can be collapsed and expanded at will.
|       - Single and Multiple selection modes.
|       - Search nodes using title and metadata as well.     
|     
|     This code is free source, but you will be kind if you don't distribute modified versions
|     with the same name, to avoid version collisions. Otherwise, please hack it!
|
|     References:
|                                                                
|     Walker II, J. Q., "A Node-Positioning Algorithm for General Trees"
|	     			   Software — Practice and Experience 10, 1980 553-561.    
|                      (Obtained from C++ User's journal. Feb. 1991)                                                                              
|					   
|     Last updated: October 26th, 2006
|     Version: 1.0
\------------------------------------------------------------------------------------------*/

/** 
@params:
id, parent id, description, width, height, color, background color, link, metadata **/
WordTreeNode = function (id, pid, dsc, size, w, h, c, sentences) {
	this.id = id;
	this.pid = pid;
	this.dsc = dsc;
	this.w = w;
	this.h = h;
	this.c = c;
	this.size = size;
	this.sentences = sentences;
	
	this.siblingIndex = 0;
	this.dbIndex = 0;
	
	this.XPosition = 0;
	this.YPosition = 0;
	this.prelim = 0;
	this.modifier = 0;
	this.leftNeighbor = null;
	this.rightNeighbor = null;
	this.nodeParent = null;	
	this.nodeChildren = [];
	
	this.isCollapsed = false;
	this.canCollapse = false;
	
	this.isSelected = false;
}

WordTreeNode.prototype._getLevel = function () {
	if (this.nodeParent.id == -1) {return 0;}
	else return this.nodeParent._getLevel() + 1;
}

WordTreeNode.prototype._isAncestorCollapsed = function () {
	if (this.nodeParent.isCollapsed) { return true; }
	else 
	{
		if (this.nodeParent.id == -1) { return false; }
		else	{ return this.nodeParent._isAncestorCollapsed(); }
	}
}

WordTreeNode.prototype._setAncestorsExpanded = function () {
	if (this.nodeParent.id == -1) { return; }
	else 
	{
		this.nodeParent.isCollapsed = false;
		return this.nodeParent._setAncestorsExpanded(); 
	}	
}

WordTreeNode.prototype._getChildrenCount = function () {
	if (this.isCollapsed) return 0;
    if(this.nodeChildren == null)
        return 0;
    else
        return this.nodeChildren.length;
}

WordTreeNode.prototype._getLeftSibling = function () {
    if(this.leftNeighbor != null && this.leftNeighbor.nodeParent == this.nodeParent)
        return this.leftNeighbor;
    else
        return null;	
}

WordTreeNode.prototype._getRightSibling = function () {
    if(this.rightNeighbor != null && this.rightNeighbor.nodeParent == this.nodeParent)
        return this.rightNeighbor;
    else
        return null;	
}

WordTreeNode.prototype._getChildAt = function (i) {
	return this.nodeChildren[i];
}

WordTreeNode.prototype._getChildrenCenter = function (tree) {
    node = this._getFirstChild();
    node1 = this._getLastChild();
    return node.prelim + ((node1.prelim - node.prelim) + tree._getNodeSize(node1)) / 2;	
}

WordTreeNode.prototype._getFirstChild = function () {
	return this._getChildAt(0);
}

WordTreeNode.prototype._getLastChild = function () {
	return this._getChildAt(this._getChildrenCount() - 1);
}

WordTreeNode.prototype._drawChildrenLinks = function (tree) {
	var s = [];
	var xa = 0, ya = 0, xb = 0, yb = 0, xc = 0, yc = 0, xd = 0, yd = 0;
	var node1 = null;
	var path = "";
	var tester = tree.paper.text(this.dsc);
	tester.attr("font-size", this.size);
	var nw = $(tester.node).outerWidth()+10;
	tester.remove();
	switch(tree.config.iRootOrientation)
	{
		case WordTree.RO_TOP:
			xa = this.XPosition + (this.w/ 2);
			ya = this.YPosition + this.h;
			break;
			
		case WordTree.RO_BOTTOM:
			xa = this.XPosition + (this.h / 2);
			ya = this.YPosition;
			break;
			
		case WordTree.RO_RIGHT:
			xa = this.XPosition;
			ya = this.YPosition + (this.h / 2);	
			break;
			
		case WordTree.RO_LEFT:
			xa = this.XPosition + this.w;
			ya = this.YPosition + (this.h / 2);	
			break;		
	}
	
	for (var k = 0; k < this.nodeChildren.length; k++){
		node1 = this.nodeChildren[k];
		path = "";
		switch(tree.config.iRootOrientation)
		{
			case WordTree.RO_TOP:
				xd = xc = node1.XPosition + (node1.w / 2);
				yd = node1.YPosition;
				xb = xa;
				switch (tree.config.iNodeJustification)
				{
					case WordTree.NJ_TOP:
						yb = yc = yd - tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_BOTTOM:
						yb = yc = ya + tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_CENTER:
						yb = yc = ya + (yd - ya) / 2;
						break;
				}
				break;
				
			case WordTree.RO_BOTTOM:
				xd = xc = node1.XPosition + (node1.w / 2);
				yd = node1.YPosition + node1.h;
				xb = xa;
				switch (tree.config.iNodeJustification)
				{
					case WordTree.NJ_TOP:
						yb = yc = yd + tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_BOTTOM:
						yb = yc = ya - tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_CENTER:
						yb = yc = yd + (ya - yd) / 2;
						break;
				}				
				break;

			case WordTree.RO_RIGHT:
				xd = node1.XPosition + node1.w;
				yd = yc = node1.YPosition + (node1.h / 2);	
				yb = ya;
				switch (tree.config.iNodeJustification)
				{
					case WordTree.NJ_TOP:
						xb = xc = xd + tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_BOTTOM:
						xb = xc = xa - tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_CENTER:
						xb = xc = xd + (xa - xd) / 2;
						break;
				}								
				break;		
				
			case WordTree.RO_LEFT:
				xd = node1.XPosition;
				yd = yc = node1.YPosition + (node1.h / 2);		
				yb = ya;
				switch (tree.config.iNodeJustification)
				{
					case WordTree.NJ_TOP:
						xb = xc = xd - tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_BOTTOM:
						xb = xc = xa + tree.config.iLevelSeparation / 2;
						break;
					case WordTree.NJ_CENTER:
						xb = xc = xa + (xd - xa) / 2;
						break;
				}								
				break;				
		}		
		
		//render the nodes;
		switch (tree.config.linkType){
			case "M":
				path = "M"+xa+" "+ya;
				path += "L"+xb+" "+yb;
				path += "L"+xc+" "+yc;
				path += "L"+xd+" "+yd;	
				break;
				
			case "B":
				path = "M"+xa+" "+ya; // move to
				path += "C"+xb+" "+yb+" "+xc+" "+yc+" "+xd+" "+yd; // smooth quadratic bezier curve
				break;					
		}		
		tree.paper.path(path).attr("stroke", tree.config.linkColor);
		
	}	
}

WordTree = function (obj, elm, width, height, paper) {
	this.config = {
		iMaxDepth : 100,
		iLevelSeparation : 20,
		iSiblingSeparation : 1,
		iSubtreeSeparation : 1,
		iRootOrientation : WordTree.RO_LEFT,
		iNodeJustification : WordTree.NJ_CENTER,
		topXAdjustment : 0,
		topYAdjustment : 0,		
		render : "AUTO",
		linkType : "B",
		linkColor : "#D68330",
		nodeColor : "rgba(0,0,0,0)",
		nodeBorderColor : "rgba(0,0,0,0)",
		defaultNodeWidth : 10,
		defaultNodeHeight : 20
	}
	
	this.version = "1.1";
	this.obj = obj;
	this.paper = paper;
	this.elm = document.getElementById(elm);
	this.self = this;
	this.ctx = null;
	this.canvasoffsetTop = 0;
	this.canvasoffsetLeft = 0;
	
	this.maxLevelHeight = [];
	this.maxLevelWidth = [];
	this.previousLevelNode = [];
	
	this.rootYOffset = 0;
	this.rootXOffset = 0;
	
	this.nDatabaseNodes = [];
	this.mapIDs = {};
	
	this.root = new WordTreeNode(-1, null, null, 2, 2);
	this.iSelectedNode = -1;
	this.iLastSearch = 0;
	
}

//Constant values

//Tree orientation
WordTree.RO_TOP = 0;
WordTree.RO_BOTTOM = 1;
WordTree.RO_RIGHT = 2;
WordTree.RO_LEFT = 3;

//Level node alignment
WordTree.NJ_TOP = 0;
WordTree.NJ_CENTER = 1;
WordTree.NJ_BOTTOM = 2;

//Node fill type
WordTree.NF_GRADIENT = 0;
WordTree.NF_FLAT = 1;

//Colorizing style
WordTree.CS_NODE = 0;
WordTree.CS_LEVEL = 1;

//Search method: Title, metadata or both
WordTree.SM_DSC = 0;
WordTree.SM_META = 1;
WordTree.SM_BOTH = 2;

//Selection mode: single, multiple, no selection
WordTree.SL_MULTIPLE = 0;
WordTree.SL_SINGLE = 1;
WordTree.SL_NONE = 2;


WordTree._canvasNodeClickHandler = function (tree,target,nodeid) {
	if (target != nodeid) return;
	tree.selectNode(nodeid,true);
}

//Layout algorithm
WordTree._firstWalk = function (tree, node, level) {
		var leftSibling = null;
        node.XPosition = tree.paper.height/2;
		node.YPosition = tree.paper.width/2;
        node.prelim = 0;
        node.modifier = 0;
        node.leftNeighbor = null;
        node.rightNeighbor = null;
        tree._setLevelHeight(node, level);
        tree._setLevelWidth(node, level);
        tree._setNeighbors(node, level);
        if(node._getChildrenCount() == 0 || level == tree.config.iMaxDepth)
        {
            leftSibling = node._getLeftSibling();
            if(leftSibling != null)
                node.prelim = leftSibling.prelim + tree._getNodeSize(leftSibling) + tree.config.iSiblingSeparation;
            else
                node.prelim = 0;
        } 
        else
        {
            var n = node._getChildrenCount();
            for(var i = 0; i < n; i++)
            {
                var iChild = node._getChildAt(i);
                WordTree._firstWalk(tree, iChild, level + 1);
            }

            var midPoint = node._getChildrenCenter(tree);
            midPoint -= tree._getNodeSize(node) / 2;
            leftSibling = node._getLeftSibling();
            if(leftSibling != null)
            {
                node.prelim = leftSibling.prelim + tree._getNodeSize(leftSibling) + tree.config.iSiblingSeparation;
                node.modifier = node.prelim - midPoint;
                WordTree._apportion(tree, node, level);
            } 
            else
            {            	
                node.prelim = midPoint;
            }
        }	
}

WordTree._apportion = function (tree, node, level) {
        var firstChild = node._getFirstChild();
        var firstChildLeftNeighbor = firstChild.leftNeighbor;
        var j = 1;
        for(var k = tree.config.iMaxDepth - level; firstChild != null && firstChildLeftNeighbor != null && j <= k;)
        {
            var modifierSumRight = 0;
            var modifierSumLeft = 0;
            var rightAncestor = firstChild;
            var leftAncestor = firstChildLeftNeighbor;
            for(var l = 0; l < j; l++)
            {
                rightAncestor = rightAncestor.nodeParent;
                leftAncestor = leftAncestor.nodeParent;
                modifierSumRight += rightAncestor.modifier;
                modifierSumLeft += leftAncestor.modifier;
            }

            var totalGap = (firstChildLeftNeighbor.prelim + modifierSumLeft + tree._getNodeSize(firstChildLeftNeighbor) + tree.config.iSubtreeSeparation) - (firstChild.prelim + modifierSumRight);
            if(totalGap > 0)
            {
                var subtreeAux = node;
                var numSubtrees = 0;
                for(; subtreeAux != null && subtreeAux != leftAncestor; subtreeAux = subtreeAux._getLeftSibling())
                    numSubtrees++;

                if(subtreeAux != null)
                {
                    var subtreeMoveAux = node;
                    var singleGap = totalGap / numSubtrees;
                    for(; subtreeMoveAux != leftAncestor; subtreeMoveAux = subtreeMoveAux._getLeftSibling())
                    {
                        subtreeMoveAux.prelim += totalGap;
                        subtreeMoveAux.modifier += totalGap;
                        totalGap -= singleGap;
                    }

                }
            }
            j++;
            if(firstChild._getChildrenCount() == 0)
                firstChild = tree._getLeftmost(node, 0, j);
            else
                firstChild = firstChild._getFirstChild();
            if(firstChild != null)
                firstChildLeftNeighbor = firstChild.leftNeighbor;
        }
}

WordTree._secondWalk = function (tree, node, level, X, Y) {
        if(level <= tree.config.iMaxDepth)
        {
            var xTmp = tree.rootXOffset + node.prelim + X ;
            var yTmp = tree.rootYOffset + Y;
            var maxsizeTmp = 0;
            var nodesizeTmp = 0;
            var flag = false;
            switch(tree.config.iRootOrientation)
            {            
	            case WordTree.RO_TOP:
	            case WordTree.RO_BOTTOM:	        	            	    	
	                nodesizeTmp = node.h;
	                //maxsizeTmp = tree.maxLevelHeight[level];	 
	               maxsizeTmp = nodesizeTmp*1.5;
	                break;

	            case WordTree.RO_RIGHT:
	            case WordTree.RO_LEFT:            
					flag = true;
	                nodesizeTmp = node.w;
					//maxsizeTmp = tree.maxLevelWidth[level];
	                maxsizeTmp = nodesizeTmp*1.5;
	                break;
            }
			if(level > -1){
            switch(tree.config.iNodeJustification)
            {
	            case WordTree.NJ_TOP:
	                node.XPosition = xTmp;
	                node.YPosition = yTmp;
	                break;
	
	            case WordTree.NJ_CENTER:
	                node.XPosition = xTmp;
	                node.YPosition = yTmp;
	                break;
	
	            case WordTree.NJ_BOTTOM:
	                node.XPosition = xTmp;
	                node.YPosition = (yTmp + maxsizeTmp) - nodesizeTmp;
	                break;
            }
			}
            if(flag)
            {
                var swapTmp = node.XPosition;
                node.XPosition = node.YPosition;
                node.YPosition = swapTmp;
            }
			if(level > -1){
            switch(tree.config.iRootOrientation)
            {
	            case WordTree.RO_BOTTOM:
	                node.YPosition = -node.YPosition - nodesizeTmp;
	                break;
	
	            case WordTree.RO_RIGHT:
	                node.XPosition = tree.paper.width -node.XPosition -nodesizeTmp;
	                break;
            }
			}
            if(node._getChildrenCount() != 0)
                WordTree._secondWalk(tree, node._getFirstChild(), level + 1, X + node.modifier, Y + maxsizeTmp + tree.config.iLevelSeparation);
            var rightSibling = node._getRightSibling();
            if(rightSibling != null)
                WordTree._secondWalk(tree, rightSibling, level, X, Y);
        }	
}

WordTree.prototype._positionTree = function () {	
	this.maxLevelHeight = [];
	this.maxLevelWidth = [];
	this.totalLevelHeight = [];
	this.totalLevelWidth = [];			
	this.previousLevelNode = [];
	WordTree._firstWalk(this.self, this.root, 0);	
	switch(this.config.iRootOrientation)
	{            
	    case WordTree.RO_TOP:
	    case WordTree.RO_LEFT: 
	    		this.rootXOffset = this.config.topXAdjustment + this.root.XPosition;
	    		this.rootYOffset = this.config.topYAdjustment + this.root.YPosition;
	        break;    
	        
	    case WordTree.RO_BOTTOM:	
	    case WordTree.RO_RIGHT: 
	    		this.rootXOffset = this.config.topXAdjustment + this.root.XPosition;
	    		this.rootYOffset = this.config.topYAdjustment + this.root.YPosition;
	}
	WordTree._secondWalk(this.self, this.root, 0, 0, 0);
	var oldY = this.nDatabaseNodes[0].YPosition;
	var oldX = this.nDatabaseNodes[0].XPosition;
	this.nDatabaseNodes[0].YPosition = this.paper.height/2;
	this.nDatabaseNodes[0].XPosition = this.paper.width/2;
	for (var n = 1; n < this.nDatabaseNodes.length; n++){ 
		node = this.nDatabaseNodes[n];
		node.XPosition = (node.XPosition-oldX) + this.nDatabaseNodes[0].XPosition;
		node.YPosition = (node.YPosition-oldY) + this.nDatabaseNodes[0].YPosition;
	}	
}

WordTree.prototype._setLevelHeight = function (node, level) {
	if (this.maxLevelHeight[level] == null){
		this.maxLevelHeight[level] = 0;
		this.totalLevelHeight[level] = 0;
	}
    if(this.maxLevelHeight[level] < node.h)
        this.maxLevelHeight[level] = node.h;
	this.totalLevelHeight[level] += node.modifier;
}

WordTree.prototype._setLevelWidth = function (node, level) {
	if (this.maxLevelWidth[level] == null){ 
		this.maxLevelWidth[level] = 0;
		this.totalLevelWidth[level] = 0;
	}
    if(this.maxLevelWidth[level] < node.w)
        this.maxLevelWidth[level] = node.w;	
	this.totalLevelWidth[level] += node.w;
}

WordTree.prototype._setNeighbors = function(node, level) {
    node.leftNeighbor = this.previousLevelNode[level];
    if(node.leftNeighbor != null)
        node.leftNeighbor.rightNeighbor = node;
    this.previousLevelNode[level] = node;	
}

WordTree.prototype._getNodeSize = function (node) {
    switch(this.config.iRootOrientation)
    {
    case WordTree.RO_TOP: 
    case WordTree.RO_BOTTOM: 
        return node.w;

    case WordTree.RO_RIGHT: 
    case WordTree.RO_LEFT: 
        return node.h;
    }
    return 0;
}

WordTree.prototype._getLeftmost = function (node, level, maxlevel) {
    if(level >= maxlevel) return node;
    if(node._getChildrenCount() == 0) return null;
    
    var n = node._getChildrenCount();
    for(var i = 0; i < n; i++)
    {
        var iChild = node._getChildAt(i);
        var leftmostDescendant = this._getLeftmost(iChild, level + 1, maxlevel);
        if(leftmostDescendant != null)
            return leftmostDescendant;
    }

    return null;	
}

WordTree.prototype._selectNodeInt = function (dbindex, flagToggle) {
	if (this.config.selectMode == WordTree.SL_SINGLE)
	{
		if ((this.iSelectedNode != dbindex) && (this.iSelectedNode != -1))
		{
			this.nDatabaseNodes[this.iSelectedNode].isSelected = false;
		}		
		this.iSelectedNode = (this.nDatabaseNodes[dbindex].isSelected && flagToggle) ? -1 : dbindex;
	}	
	this.nDatabaseNodes[dbindex].isSelected = (flagToggle) ? !this.nDatabaseNodes[dbindex].isSelected : true;	
}

WordTree.prototype._collapseAllInt = function (flag) {
	var node = null;
	for (var n = 0; n < this.nDatabaseNodes.length; n++)
	{ 
		node = this.nDatabaseNodes[n];
		if (node.canCollapse) node.isCollapsed = flag;
	}	
	this.UpdateTree();
}

WordTree.prototype._selectAllInt = function (flag) {
	var node = null;
	for (var k = 0; k < this.nDatabaseNodes.length; k++)
	{ 
		node = this.nDatabaseNodes[k];
		node.isSelected = flag;
	}	
	this.iSelectedNode = -1;
	this.UpdateTree();
}

WordTree.prototype._drawTree = function () {
	//this.paper.clear();
	var node = null;
	var color = "";
	var border = "";
	var rect, text;
	var set = [];
	for (var n = 0; n < this.nDatabaseNodes.length; n++){ 
		node = this.nDatabaseNodes[n];
		if (!node._isAncestorCollapsed()){
			set = this.paper.set();
			rect = this.paper.rect(node.XPosition, node.YPosition, node.trueWidth, node.trueHeight)
			.attr("stroke", node.c);
			$(rect.node).attr("trueWidth", node.trueWidth);
			text = this.paper.text(node.XPosition, node.YPosition+node.h/2, node.dsc)
			.attr("font-size", node.size)
			.attr("text-anchor", "start");
			$(text.node).attr("c", "black");
			$(text.node).data("sentences", node.sentences);
			for(var i = 0; i < node.sentences.length; i++){
				register(node.sentences[i], text, "wordtree");
			}
			if(node._getChildrenCount() == 0){
				text.attr("fill", "#888");
				$(text.node).attr("c", "#888");
			}
			//interactions
			text.click(function(){
				$('rect[c]').each(function(){
					$(this).attr("fill", $(this).attr("c"));
				})
				$('text[c]').each(function(){
					$(this).attr("fill", $(this).attr("c"));
				})
				var sentences = $(this.node).data("sentences");
				for(var i = 0; i< sentences.length; i++){
					var heatMapPoints = getRegistered(sentences[i], "heatmap");
					for(var j = 0; j < heatMapPoints.length; j++){
						heatMapPoints[j].attr("fill", "orange");
					}
					var wordTreeNeighbors = getRegistered(sentences[i], "wordtree");
					for(var j = 0; j < wordTreeNeighbors.length; j++){
						wordTreeNeighbors[j].attr("fill", "brown");
					}
				}
				this.attr("fill", "orange");
			})					
			if (!node.isCollapsed)	node._drawChildrenLinks(this.self);
		}
	}	
}


// WordTree API begins here...

WordTree.prototype.UpdateTree = function () {
	this._positionTree();	
	this._drawTree();	
}

WordTree.prototype.add = function (id, pid, dsc, size, c, sentences) {	
	var color = c || this.config.nodeColor;
	var tg = (typeof target != "undefined")?  target : "";
	var metadata = (typeof meta != "undefined")	? meta : "";
	var pnode = null; //Search for parent node in database
	if (pid == -1) 
		{
			pnode = this.root;
		}
	else
		{
			for (var k = 0; k < this.nDatabaseNodes.length; k++)
			{
				if (this.nDatabaseNodes[k].id == pid)
				{
					pnode = this.nDatabaseNodes[k];
					break;
				}
			}	
		}
	tester = this.paper.text(0, 0, dsc);
	tester.attr("font-size", size);
	nw = tester.getBBox().width;
	nh = tester.getBBox().height;
	var node = new WordTreeNode(id, pid, dsc, size, nw, nh, color, sentences);	//New node creation...
	tester.remove();
	node.nodeParent = pnode;  //Set it's parent
	pnode.canCollapse = true; //It's obvious that now the parent can collapse	
	var i = this.nDatabaseNodes.length;	//Save it in database
	node.dbIndex = this.mapIDs[id] = i;	 
	this.nDatabaseNodes[i] = node;	
	var h = pnode.nodeChildren.length; //Add it as child of it's parent
	node.siblingIndex = h;
	pnode.nodeChildren[h] = node;
}

WordTree.prototype.collapseAll = function () {
	this._collapseAllInt(true);
}

WordTree.prototype.expandAll = function () {
	this._collapseAllInt(false);
}

WordTree.prototype.collapseNode = function (nodeid, upd) {
	var dbindex = this.mapIDs[nodeid];
	this.nDatabaseNodes[dbindex].isCollapsed = !this.nDatabaseNodes[dbindex].isCollapsed;
	if (upd) this.UpdateTree();
}
