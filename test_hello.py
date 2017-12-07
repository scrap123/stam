import mathlib


def test_total1():
    res = mathlib.calc_total(2, 3)
    assert res==5


def test_total2():
    res = mathlib.calc_total(2, 3)
    assert res==5


def test_total3():
    res = mathlib.calc_total(2, 10)
    assert res==12


def test_total4():
    res = mathlib.calc_total(2, 130)
    assert res==132
