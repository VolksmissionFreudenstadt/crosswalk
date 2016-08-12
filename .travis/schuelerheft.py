# Produces a PDF for the SLA passed as a parameter.
# Uses the same file name and replaces the .sla extension with .pdf
#
# usage:
# scribus -g -py to-pdf.py file.sla

import os

if scribus.haveDoc() :
	scribus.setLayerPrintable("Lösungen", false)
    filename = os.path.splitext(scribus.getDocName())[0]
    pdf = scribus.PDFfile()
    pdf.file = filename+".pdf"
    pdf.save()
else :
    print("Failed to open file.")