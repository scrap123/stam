import requests
import pytest


@pytest.fixture(scope='module', autouse=True)
def clean_db():
    print ('\nclean_db')
    requests.post('http://localhost:8080/init')
    yield
    print('i am done clean_db')


@pytest.fixture(scope='module')
def empid():
    print ('\nempid')
    employee = requests.post('http://localhost:8080/employee', json={'name': 'blazedude', 'imageUrl': 'http://wfwef'})
    yield employee.json().get('result').get('id')
    print('i am done empid')


@pytest.fixture(scope='module')
def order_module(empid):
    print ('\norder_module')
    order = requests.post('http://localhost:8080/order',
                          json={'saladType': 2,
                                'comments': 'comment_of_module_function',
                                'employeeId': empid,
                                'sauceIds': [1, 2, 5],
                                'vegetableIds': [7]})
    yield order


@pytest.fixture(scope='function')
def order_function(empid):
    print ('\norder_function')
    order = requests.post('http://localhost:8080/order',
                          json={'saladType': 2,
                                'comments': 'comment_of_order_function',
                                'employeeId': empid,
                                'sauceIds': [1, 2, 5],
                                'vegetableIds': [7]})
    yield order


def test_create(empid):
    order = requests.post('http://localhost:8080/order',
                          json={'saladType': 2,
                                'comments': 'dumb comment',
                                'employeeId': empid,
                                'sauceIds': [1, 2, 5],
                                'vegetableIds': [7, 6]})
    print ('\ntest_create')
    print (order.json().get('result').get('id'))
    order_get = requests.get('http://localhost:8080/order')
    print ('\norderGet')
    order_get_json = order_get.json()
    element_added = order_get_json.get('result')
    element_added = element_added[len(element_added) - 1]

    assert element_added.get('id') == order.json().get('result').get('id')
    assert element_added.get('comments') == order.json().get('result').get('comments')


def test_read(order_module):
    print ('\ntest_read')
    order_get = requests.get('http://localhost:8080/order')
    print ('\norderGet ')
    order_get_json = order_get.json()
    element_added = order_get_json.get('result')
    element_added = element_added[len(element_added) - 1]
    print ('asserting the comment of function')
    assert element_added.get('comments') == 'comment_of_module_function'


def test_update(order_function):
    print ('\ntest_update')
    id = (order_function.json().get('result').get('id'))
    url = 'http://localhost:8080/order/%d' % id
    print (url)
    requests.patch(url,
                                  json={'saladType': 2,
                                        'comments': 'a new comment from test_update 5678',
                                        'sauceIds': [1, 2, 5],
                                        'vegetableIds': [7, 6]})

    order_get = requests.get('http://localhost:8080/order')
    print ('\norderGet ')
    order_get_json = order_get.json()
    element_modified = order_get_json.get('result')
    element_modified = element_modified[len(element_modified) - 1]
    print (element_modified)
    assert element_modified.get('comments') == 'a new comment from test_update 5678'


def test_delete(order_function):
    print ('\ntest_delete')
    order_id = (order_function.json().get('result').get('id'))
    url = 'http://localhost:8080/order/%d' % order_id
    response = requests.get(url)
    assert response.status_code == 200
    response = requests.delete(url)
    assert response.status_code == 204
    response = requests.get(url)
    assert response.status_code == 404
