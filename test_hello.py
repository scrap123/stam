import mathlib

def test_tota1():
    res = mathlib.calc_total(2, 3)
    assert res==5


def test_tota2():
    res = mathlib.calc_total(2, 3)
    assert res==6


def test_total3():
    res = mathlib.calc_total(2, 10)
    assert res==12
