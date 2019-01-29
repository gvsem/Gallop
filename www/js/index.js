var jsdom = require("jsdom");
const { JSDOM } = jsdom;
const { window } = new JSDOM();
const { document } = (new JSDOM('')).window;
global.document = document;

var $ = jQuery = require('jquery')(window);





const express = require('express')
const app = express()
const port = 3000
app.get('/busradar/:date/:from/:to', (req, resp) => {


    //response.write('Hello from Express!');







    (function (callback) {
        'use strict';
            
        const httpTransport = require('https');
        const responseEncoding = 'utf8';
        const httpOptions = {
            hostname: 'www.busradar.com',
            port: '443',
            path: '/api2/signalr/negotiate?clientProtocol=1.5&culture=en&currency=EUR&connectionData=%5B%7B%22name%22:%22searchhub%22%7D%5D',
            method: 'GET',
            headers: {"Cookie":"CookieDetector=; _ga=GA1.2.1517559395.1533252451; _gid=GA1.2.952200896.1534070833; _gat=1","X-Language":"en","Accept-Encoding":"gzip, deflate, br","Accept-Language":"ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7","User-Agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36","Content-Type":"application/json; charset=UTF-8","Accept":"text/plain, */*; q=0.01","Referer":"https://www.busradar.com/search/?From=Moscow&To=Saint+Petersburg&When=2018-08-13&WhenReturn=&Passengers=1","X-Requested-With":"XMLHttpRequest","Connection":"keep-alive"}
        };
        httpOptions.headers['User-Agent'] = 'node ' + process.version;
     
        // Paw Store Cookies option is not supported
    
        const request = httpTransport.request(httpOptions, (res) => {
            let responseBufs = [];
            let responseStr = '';
            
            res.on('data', (chunk) => {
                if (Buffer.isBuffer(chunk)) {
                    responseBufs.push(chunk);
                }
                else {
                    responseStr = responseStr + chunk;            
                }
            }).on('end', () => {
                responseStr = responseBufs.length > 0 ? 
                    Buffer.concat(responseBufs).toString(responseEncoding) : responseStr;
                
                callback(null, res.statusCode, res.headers, responseStr);
            });
            
        })
        .setTimeout(0)
        .on('error', (error) => {
            callback(error);
        });
        request.write("")
        request.end();
        
    
    })((error, statusCode, headers, body) => {
        console.log('ERROR:', error); 
        console.log('STATUS:', statusCode);
        console.log('HEADERS:', JSON.stringify(headers));
        console.log('BODY:', body);


        var token = JSON.parse(body)['ConnectionToken'];

        const WebSocket = require('ws');
        const ws = new WebSocket('wss://www.busradar.com/api2/signalr/connect?transport=webSockets&clientProtocol=1.5&culture=en&currency=EUR&connectionToken=' + encodeURIComponent(token) + '&connectionData=%5B%7B%22name%22%3A%22searchhub%22%7D%5D&tid=5');
        
        ws.on('open', function open() {
            ws.send('{"H":"searchhub","M":"Search2","A":["' + req.params.date + '","' + req.params.from + '","' + req.params.to + '",1,"",0,15000,0,"1"],"I":0}');
            console.log('sent')
        });
        
        var results = JSON.parse("[]");

        ws.on('message', function incoming(data) {

            console.log(data.substring(0,90))

            var json = JSON.parse(data);

            if (typeof (json.M) !== 'undefined') {
                if (typeof (json.M[0]) !== 'undefined') {
                    if (json.M[0].M == "SearchResults") {
                        results = results.concat(json.M[0].A[0]);
                    }
                }
            }

            if (data == '{"R":true,"I":"0"}') {
                resp.status(200).send(JSON.stringify(results));
                ws.close()
            }

        });




    });


    





})
app.listen(port, (err) => {
    if (err) {
        return console.log('something bad happened', err)
    }
    console.log(`server is listening on ${port}`)
})







//wss://www.busradar.com/api2/signalr/connect?transport=webSockets&clientProtocol=1.5&culture=en&currency=EUR&connectionToken=2%2BD1KGgnO1bL0ZVNLldPZ7HSACfJ76%2Fa05DOmwd%2BYZUn6rJISsnWPSH6QxGieNEQJ0kNPv9uTJrMgkvHF0N386ikKSfWySk3fwhjKtv5avr1YMNH&connectionData=%5B%7B%22name%22%3A%22searchhub%22%7D%5D&tid=5

/*$(function () {
    // if user is running mozilla then use it's built-in WebSocket
    //window.WebSocket = window.WebSocket;
  
    var connection = new WebSocket('wss://www.busradar.com/api2/signalr/connect?transport=webSockets&clientProtocol=1.5&culture=en&currency=EUR&connectionToken=2%2BD1KGgnO1bL0ZVNLldPZ7HSACfJ76%2Fa05DOmwd%2BYZUn6rJISsnWPSH6QxGieNEQJ0kNPv9uTJrMgkvHF0N386ikKSfWySk3fwhjKtv5avr1YMNH&connectionData=%5B%7B%22name%22%3A%22searchhub%22%7D%5D&tid=5');
  
    connection.onopen = function () {
      // connection is opened and ready to use
      response.send('opened!')
    };
  
    connection.onerror = function (error) {
        response.send('error!')
      // an error occurred when sending/receiving data
    };
  
    connection.onmessage = function (message) {
      // try to decode json (I assume that each message
      // from server is json)
      try {
        var json = JSON.parse(message.data);
        response.send('This doesn\'t look like a valid JSON: ', message.data);
      } catch (e) {
        response.send('This doesn\'t look like a valid JSON: ',
            message.data);
        return;
      }
      // handle incoming message
    };
  });

*/