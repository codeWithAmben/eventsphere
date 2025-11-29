<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <!-- Basic XSL to transform a <ticket> XML into HTML for printing or PDF generation -->
  <xsl:output method="html" encoding="UTF-8"/>

  <xsl:template match="/">
    <html>
      <head>
        <meta charset="utf-8"/>
        <title>Ticket</title>
        <style>
          body { font-family: Arial, sans-serif; }
          .ticket { border: 2px dashed #333; padding: 20px; display: block; width: 600px; }
          .header { font-weight:bold; font-size: 1.25em; }
        </style>
      </head>
      <body>
        <div class="ticket">
          <div class="header"><xsl:value-of select="ticket/title"/></div>
          <div>Event: <xsl:value-of select="ticket/event_id"/> </div>
          <div>Name: <xsl:value-of select="ticket/name"/> </div>
          <div>Issued: <xsl:value-of select="ticket/issued"/> </div>
          <div>Ticket ID: <xsl:value-of select="ticket/@id"/> </div>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
