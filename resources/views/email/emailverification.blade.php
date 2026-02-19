<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Email Verification</title>
    <style type="text/css" rel="stylesheet" media="all">
    /* Base ------------------------------ */
    
    *:not(br):not(tr):not(html) {
      font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
      box-sizing: border-box;
    }
    
    body {
      width: 100% !important;
      height: 100%;
      margin: 0;
      line-height: 1.4;
      background-color: #F2F4F6;
      color: #74787E;
      -webkit-text-size-adjust: none;
    }
    
    p,
    ul,
    ol,
    blockquote {
      line-height: 1.4;
      text-align: left;
    }
    
    a {
      color: #3869D4;
    }
    
    a img {
      border: none;
      width: 25%;
    }
    
    td {
      word-break: break-word;
    }
    /* Layout ------------------------------ */
    
    .email-wrapper {
      width: 100%;
      margin: 0;
      padding: 0;
      -premailer-width: 100%;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
      background-color: #F2F4F6;
    }
    
    .email-content {
      width: 100%;
      margin: 0;
      padding: 0;
      -premailer-width: 100%;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
    }
    /* Masthead ----------------------- */
    
    .email-masthead {
      text-align: center;
    }
    
    .email-masthead_logo {
      width: 94px;
    }
    
    .email-masthead_name {

      font-size: 16px;
      font-weight: bold;
      color: #bbbfc3;
      text-decoration: none;
      text-shadow: 0 1px 0 white;
    }
    /* Body ------------------------------ */
    
    .email-body {
      width: 100%;
      margin: 0;
      padding: 0;
      -premailer-width: 100%;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
      border-top: 1px solid #EDEFF2;
      border-bottom: 1px solid #EDEFF2;
      background-color: #FFFFFF;
    }
    
    .email-body_inner {
      width: 570px;
      margin: 0 auto;
      padding: 0;
      -premailer-width: 570px;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
      background-color: #FFFFFF;
    }
    
    .email-footer {
      width: 570px;
      margin: 0 auto;
      padding: 0;
      -premailer-width: 570px;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
      text-align: center;
    }
    
    .email-footer p {
      color: #AEAEAE;
    }
    
    .body-action {
      width: 100%;
      margin: 30px auto;
      padding: 0;
      -premailer-width: 100%;
      -premailer-cellpadding: 0;
      -premailer-cellspacing: 0;
      text-align: center;
    }
    
    .body-sub {
      margin-top: 25px;
      padding-top: 25px;
      border-top: 1px solid #EDEFF2;
    }
    
    .content-cell {
      padding: 35px;
    }
    
    .preheader {
      display: none !important;
      visibility: hidden;
      mso-hide: all;
      font-size: 1px;
      line-height: 1px;
      max-height: 0;
      max-width: 0;
      opacity: 0;
      overflow: hidden;
    }

    /* Utilities ------------------------------ */
    
    .align-right {
      text-align: right;
    }
    
    .align-left {
      text-align: left;
    }
    
    .align-center {
      text-align: center;
    }
    /*Media Queries ------------------------------ */
    
    @media only screen and (max-width: 600px) {
      .email-body_inner,
      .email-footer {
        width: 100% !important;
      }
    }
    
    @media only screen and (max-width: 500px) {
      .button {
        width: 100% !important;
      }
    }
    /* Buttons ------------------------------ */
    
    .button {
        color: #c9c9c9;
        justify-content: center;
        align-items: center;
        display: inline-block;
        line-height: 50px;
        height: 50px;
        background: #d10000;
        padding: 0 35px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        -webkit-animation-delay: 2.4s;
        animation-delay: 2.4s;
        text-align: center;
        margin-right: 12px;
        text-decoration: none;

    }
    .button:hover {
        color     : #fff;
    }
    
    .button--green {
      background-color: #72b741;
      border-top: 10px solid #72b741;
      border-right: 18px solid #72b741;
      border-bottom: 10px solid #72b741;
      border-left: 18px solid #72b741;
    }
    
    .button--red {
      background-color: #FF6136;
      border-top: 10px solid #FF6136;
      border-right: 18px solid #FF6136;
      border-bottom: 10px solid #FF6136;
      border-left: 18px solid #FF6136;
    }
    .img
    {
    	max-width: 100%; 
    	height: auto !important;
    }
    /* Type ------------------------------ */
    
    h1 {
      margin-top: 0;
      color: #2F3133;
      font-size: 19px;
      font-weight: bold;
      text-align: left;
    }
    
    h2 {
      margin-top: 0;
      color: #2F3133;
      font-size: 16px;
      font-weight: bold;
      text-align: left;
    }
    
    h3 {
      margin-top: 0;
      color: #2F3133;
      font-size: 14px;
      font-weight: bold;
      text-align: left;
    }
    
    p {
      margin-top: 0;
      color: #74787E;
      font-size: 16px;
      line-height: 1.5em;
      text-align: left;
    }
    
    p.sub {
      font-size: 12px;
    }
    
    p.center {
      text-align: center;
    }
    </style>
  </head>
  <body>
    <span class="preheader">Use this link to verify your Amazing Wealth Worldwide Corp. Account.</span>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center">
          <table class="email-content" width="100%" cellpadding="0" cellspacing="0">
            <tr style="text-align: center">
              <td class="email-masthead">
              <a href="https://mrsiomai.com" class="email-masthead_name">
                	<img class="img" src="https://mr-siomai.mlm-storage.sg-sin1.upcloudobjects.com/mlm/item_thumbnail/S88X1BOgOoiMGUMgfQzq7rZjgr76pIIrkai4DxNP.png">
      		    	</a>
              </td>
            </tr>
            <!-- Email Body -->
            <tr>
              <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
                  <!-- Body content -->
                  <tr>
                    <td class="content-cell">
                      <h1 style="text-transform: capitalize;">Hi {{$name}},</h1>
                      <p>In order to start your <b>Amazing Wealth Worldwide Corp.</b> account, you need to confirm your email address. Use the button below to verify your account or click 
                      
                      @if ($http_host == "mrsiomai.test")
                        <a href="http://localhost:4200/member/email/activated/{{$code}}/{{$id}}" target="_blank">here.</a>

                      @elseif ($http_host == "prod-api.mrsiomai.com")
                        <a href="https://mrsiomai.com/member/email/activated/{{$code}}/{{$id}}" target="_blank">here.</a>

                        @elseif ($http_host == "staging-api.mrsiomai.com")
                        <a href="https://staging-app.mrsiomai.com/member/email/activated/{{$code}}/{{$id}}" target="_blank">here.</a>
                      @endif
                    </strong></p>
                      <!-- Action -->
                      <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                          <td align="center">
                            <!-- Border based button
                       https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                <td align="center">
                                  <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td>
                                        @if ($http_host == "mrsiomai.test")
                                          <a href="http://localhost:4200/member/email/activated/{{$code}}/{{$id}}" target="_blank" style="color: white;
                                          justify-content: center;
                                          align-items: center;
                                          display: inline-block;
                                          line-height: 50px;
                                          height: 50px;
                                          background: #c99b3d;
                                          border-radius: 54px;
                                          padding: 0 35px;
                                          font-weight: 700;
                                          color: #fff;
                                          text-transform: uppercase;
                                          -webkit-animation-delay: 2.4s;
                                          animation-delay: 2.4s;
                                          text-align: center;
                                          margin-right: 12px;
                                          text-decoration: none;">Verify email address</a>

                                        @elseif ($http_host == "prod-api.mrsiomai.com")
                                          <a href="https://mrsiomai.com/member/email/activated/{{$code}}/{{$id}}" target="_blank" style="color: white;
                                          justify-content: center;
                                          align-items: center;
                                          display: inline-block;
                                          line-height: 50px;
                                          height: 50px;
                                          background: #c99b3d;
                                          border-radius: 54px;
                                          padding: 0 35px;
                                          font-weight: 700;
                                          color: #fff;
                                          text-transform: uppercase;
                                          -webkit-animation-delay: 2.4s;
                                          animation-delay: 2.4s;
                                          text-align: center;
                                          margin-right: 12px;
                                          text-decoration: none;">Verify email address</a>

                                          @elseif ($http_host == "staging-api.mrsiomai.com")
                                          <a href="https://staging-app.mrsiomai.com/member/email/activated/{{$code}}/{{$id}}" target="_blank" style="color: white;
                                          justify-content: center;
                                          align-items: center;
                                          display: inline-block;
                                          line-height: 50px;
                                          height: 50px;
                                          background: #c99b3d;
                                          border-radius: 54px;
                                          padding: 0 35px;
                                          font-weight: 700;
                                          color: #fff;
                                          text-transform: uppercase;
                                          -webkit-animation-delay: 2.4s;
                                          animation-delay: 2.4s;
                                          text-align: center;
                                          margin-right: 12px;
                                          text-decoration: none;">Verify email address</a>
                                        @endif
                                        <!-- <a href="http://localhost:4200/member/email/activated/{{$code}}/{{$id}}" class="button" target="_blank" style="color: white">Verify email address</a> -->
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0">
                  <tr>
                    <td class="content-cell" align="center">
                      <p class="sub align-center">&copy; 2022 Amazing Wealth Worldwide Corp.. All Right Reserved.</p>
                      <!-- <p class="sub align-center">
                        Powered By: Digima Web Solutions, Inc.
                      </p> -->
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>