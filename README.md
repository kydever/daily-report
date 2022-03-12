# 日报系统

## 测试飞书授权

http://127.0.0.1:9501/oauth/authorize?redirect_uri=http://127.0.0.1:9501/oauth/login

## 接口文档

### 授权

> GET /oauth/authorize

- 入参

| 参数         | 类型   | 备注         |
| ------------ | ------ | ------------ |
| redirect_uri | String | 重定向的地址 |

### 登录

> POST /oauth/login

- 入参

| 参数 | 类型   | 备注                    |
| ---- | ------ | ----------------------- |
| code | String | 重定向后自动携带的 Code |

- 出参

| 参数  | 类型   | 备注               |
| ----- | ------ | ------------------ |
| token | String | 登录后返回的 Token |

### 创建日报条目

> POST /report/item

- 入参

| 参数       | 类型    | 备注            |
| ---------- | ------- | --------------- |
| id         | Integer | 条目ID，新增填0 |
| project    | String  | 项目            |
| module     | String  | 模块            |
| summary    | String  | 描述            |
| begin_time | String  | 开始时间(10:00) |
| end_time   | String  | 结束时间(11:00) |

- 出参

| 参数 | 类型    | 备注   |
| ---- | ------- | ------ |
| id   | Integer | 条目ID |
