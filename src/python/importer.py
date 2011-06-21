"""
	importer.py
	
	take a collection of documents arranged one sentence per line and import 
	into a wordseer style db.
	
	@author: Aditi Muralidharan
	@email aditi@cs.berkeley.edu
	Last modified: June 19th 2011
"""

from stanford_parser.parser import Parser

class Importer:
	def __init__(self):
		self.parser = Parser();
	
	def parseOneText(text):
		