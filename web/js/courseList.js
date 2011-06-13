/*
    $Id: courseList.js 12916 2011-03-03 10:43:35Z abourguignon $
 */

$(document).ready(function(){
    $("img.qtip").each(function()
    {
        $(this).qtip({
            content: $(this).attr("alt"),
            
            show: "mouseover",
            hide: "mouseout",
            position: {
                corner: {
                    target: "topRight",
                    tooltip: "bottomRight"
                }
            },
            
            style: {
                width: "auto",
                padding: 5,
                background: "#CCDDEE",
                color: "black",
                fontSize: "0.9em",
                textAlign: "center",
                border: {
                    width: 7,
                    radius: 5,
                    color: "#CCDDEE"
                },
                tip: "bottomLeft"
            },
           position: {
              corner: {
                 target: "topRight",
                 tooltip: "bottomLeft"
              }
           }
        });
    });
});