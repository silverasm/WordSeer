/***** Here are all the variables that maintain 
***** the state of this program 
****/


/*** Part 1: Viewing a narrative and selecting patterns
***/
var highlighter = false;
var highlightedSentence = -1;
var highlightStart = -1;
var highlightEnd = -1;
var highlightPosition = -1;
var textPattern = ""; // the string of text that's highlighted
var detected = []; // the dependencies within the selected text.
var highlighted = false; // the highligted document element.
var narrativeID = -1;
var startSentence = -1;
var startPosition = -1;
var endSentence = -1;
var endPosition = -1;

/** Part 1.a: Highlighting a pattern in the text
***/
var type = "text";
var narratives = [];
var narrativeFrames = {};
var highlightStartY = -1;

/*** Part 2: Search results
**** Here are all the state variables and global parameters
**** that control the search results page
***/

var w = 420; // the width of the frequency graph
var h = 220; // the height of the frequency graph
var maxNum = 30;
var graphs = [];
var graphData = [];
var page = 0;
var currentGov = "";
var currentDep = "";
var currentRel = "";
var currentQ = "";
var minGraphSize = 1; // how many data points before you draw a graph?

/** Part 2b: The random sentence graphics
**/

var randomSentence = "";
var randomDependencies = "";

/*** Part 3: Saving patterns
***/
var saved = [];

/** Part 3b: Annotating narratives**/
var highlightID = -1;
var oldNote = "";
var oldTags = "";
var allTags = "";

/** Part 3c: Salient words in paragraphs*/
var currentParagraph = -1;
var numSalientWords = 15;
var heatMapURL = "heatmap.php?filter=all&unit=paragraphs&words="
var heatMapQuery = "";
//toy data
var data_gov = [];
var data_dep = [];

/*** Part 4: Heat map
***/
var narrativeIDList = [];
var selectionInfo = {narrativeID:-1, section:-1}
var heatMapWidth = 700;
var heatMapHeight = 350;
var paper = {}; // Raphael("heatmap", heatMapWidth, heatMapHeight);
var granularity = 100;
var heatMapColors = ["aqua", "gold", "yellowgreen", "fuchsia"];
var currentHeatMapColor = 0;
var overlay = false;
var markColors = ["red", "fuchsia"]
var currentMarkColor;
var oldColor;
var blockWidth;
var blockHeight;
var sentencesPerBlock = 30; // sentences per block
var paragraphsPerBlock = 5; // paragraphs per block
var unit;
var concordanceLength = 7; // number of words on each side.
windowLoaded = false;
_register = {};// objects that are associated with various passages
