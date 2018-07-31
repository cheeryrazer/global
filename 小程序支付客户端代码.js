dopay: function () {
	console.log('发起微信支付申请');

	// 1.登录：
	wx.login({
		success: function (res) {
			if (res.code) {
				//2.code 换取session_key:
				// wx.request({
				//   url: CFG.WX_PAY_HOST+'session_key',
				//   data:{code:res.code},
				//   success: function (res) {
				//     console.log(res);
				//   },
				//   fail: function (e) {
				//     console.log('失败');
				//     console.log(e);
				//   }
				// })

				// 假如已经获取到了 ， 有效期：30天
				var session_key = { "session_key": "anVv7lfSlrUo1S44LZ3xxxxx", "expires_in": 2592000, "openid": "oahAJ0etPchBhPnojFmzJ6Bxxxxx" };

				// 3.【统一下单API】文档地址 https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_4&index=2
				wx.request({
					url: CFG.WX_PAY_HOST + 'payOrder',
					data: session_key,
					method: 'GET',
					// header: {}, // 设置请求的 header
					success: function (res) {
						// success
						console.log(res);
						var prepay_id = res.data.prepay_id;
						if (undefined !== prepay_id && prepay_id) {
							
							// 4.获取paySign
							wx.request({
								url: CFG.WX_PAY_HOST + 'requestPayment',
								data: { 'prepay_id': prepay_id },
								method: 'GET',
								// header: {}, // 设置请求的 header
								success: function (res) {
									// success
									console.log(res);
									var data = res.data;
									var paySign = data.paySign;
									if (undefined !== paySign && paySign) {

										// 5. 微信支付调用
										var payObj = {
												timeStamp: data.timeStamp,
												nonceStr: data.nonceStr,
												package: data.package,
												signType: data.signType,
												paySign: paySign,
												success: function (res) {
													// success
													console.log(res);
												},
												fail: function (e) {
													// fail
													console.log(e);
												}
										};
										console.log(payObj);
										wx.requestPayment(payObj);

									} else {
										console.log('未获取到paySign');
									}
								},
								fail: function (e) {
									// fail
									console.log(e);
								}
							})
						} else {
							console.log('未获取到prepay_id')
						}
					},
					fail: function (e) {
						// fail
						cnosole.log(e);
					}
				})
			} else {
				console.log('获取用户登录态失败！' + res.errMsg)
			}
		}
	});
},